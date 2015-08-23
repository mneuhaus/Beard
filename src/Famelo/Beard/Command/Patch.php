<?php

namespace Famelo\Beard\Command;

use Herrera\Version\Comparator;
use Herrera\Version\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Patch command.
 *
 */
class Patch extends Command {

	/**
	 * The output handler.
	 *
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * @var string
	 */
	protected $baseDir;

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('patch');
		$this->setDescription('Patch and update repositories based on beard.json');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->baseDir = getcwd();

		$configFile = 'beard.json';

		if (!file_exists($configFile)) {
			if (file_exists('gerrit.json') && file_exists('Packages/Libraries/composer/autoload_namespaces.php')) {
				$data = file_get_contents('gerrit.json');
				$namespaces = include_once('Packages/Libraries/composer/autoload_namespaces.php');
				$packages = json_decode($data);
				foreach ($packages as $packageName => $changes) {
					$namespace = str_replace('.', '\\', $packageName);
					$path = $namespaces[$namespace][0];

					foreach ($changes as $name => $changeId) {
						$change = new \StdClass();
						$change->type = 'gerrit';
						$change->name = $name;
						$change->gerrit_git = 'git.typo3.org';
						$change->gerrit_api_endpoint = 'https://review.typo3.org/';
						$change->path = $path;
						$change->change_id = $changeId;
						$change->patch_set = NULL;

						$this->applyChange($change);
					}
				}
				#var_dump($packages, $namespaces);
				exit();
			} else {
				$this->output->write('<comment>No beard.json found!</comment>' . chr(10));
				return;
			}
		} else {
			$config = $this->getConfig($configFile);

			$this->baseDir = getcwd();
			foreach ($config->getChanges() as $change) {
				if (isset($change->path) && $change->path !== NULL) {
					$this->applyChange($change);
				} else {
					$this->applyGerritTopic($change);
				}
			}
		}
	}

	public function getConfig($filename) {
		$helper = $this->getHelper('config');
		return $helper->loadFile($filename);
	}

	public function applyChange($change) {
		$repository = str_replace($this->baseDir . '/', '', $change->path);
		$this->output->writeln($repository . ': ' . $change->name);

		if (!is_dir($change->path)) {
			$this->output->writeln('<error>The directory ' . $change->path . ' doesn\'t exist!</error>');
			return;
		}

		chdir($change->path);

		switch($change->type) {
			case 'gerrit': $this->applyGerritChange($change);
				break;
			case 'github': $this->applyGithubChange($change);
				break;
			case 'patch':
			case 'diff': $this->applyDiffChange($change);
				break;
			default: $this->output->write('woot? Sorry, i don\'t know that kind of type: ' . $change->type);
		}

		chdir($this->baseDir);
		$this->output->writeln('');
	}

	public function findPath($gerritChange, $paths) {
		if (isset($paths[$gerritChange->project])) {
			return $paths[$gerritChange->project];
		}

		if (stristr($gerritChange->project, 'Packages/')) {
			$project = str_replace('Packages/', '', $gerritChange->project);
			$packageFolders = scandir('Packages');
			foreach ($packageFolders as $packageFolder) {
				if (substr($packageFolder, 0, 1) === '.') {
					continue;
				}
				$possiblePath = 'Packages/' . $packageFolder . '/' . $project;
				if (is_dir($possiblePath)) {
					return $possiblePath;
				}
			}
		}
		$this->output->writeln('<comment>Could not find a path for the Package ' . $gerritChange->project . ', please configure the path in the topic:paths configuration</comment>');

		return NULL;
	}

	public function applyGerritTopic($topic) {
		$changes = $this->fetchTopicChanges($topic);
		$paths = array();
		if (isset($topic->paths)) {
			$paths = get_object_vars($topic->paths);
		}
		foreach ($changes as $gerritChange) {
			$change = clone $topic;
			$change->name = $gerritChange->subject;
			$change->type = 'gerrit';
			$change->change_id = $gerritChange->_number;
			$change->path = $this->findPath($gerritChange, $paths);
			if ($change->path !== NULL) {
				$this->output->writeln('');
				$this->applyChange($change);
			}
		}
	}

	public function applyGerritChange($change) {
		$changeInformation = $this->fetchChangeInformation($change);

		if (strpos($change->change_id, ',') !== FALSE) {
			list($changeId, $patchSet) = explode(',', $change->change_id);
			$change->change_id = $changeId;
			$change->patch_set = $patchSet;
		}

		$merge = TRUE;

		if ($changeInformation->status == 'MERGED') {
			$this->output->write('<comment>This change has been merged!</comment>' . chr(10));
		} elseif ($changeInformation->status == 'ABANDONED') {
			$this->output->write('<error>This change has been abandoned!</error>' . chr(10));
		}

		if ($merge === TRUE) {
			$ref = $changeInformation->revisions->{$changeInformation->current_revision}->fetch->{'anonymous http'}->ref;
			if (isset($change->patch_set)) {
				$explodedRef = explode('/', $ref);
				array_pop($explodedRef);
				$explodedRef[] = $change->patch_set;
				$ref = implode('/', $explodedRef);
			}

			$command = 'git fetch --quiet git://' . $change->gerrit_git . '/' . $changeInformation->project . ' ' . $ref . '';

			$output = $this->executeShellCommand($command);

			$commit = $this->executeShellCommand('git log --format="%H" -n1 FETCH_HEAD');

			if ($this->isCommitAlreadyPicked($commit) === TRUE) {
				$this->output->write('<comment>Already picked</comment>' . chr(10));
			} else {
				echo $output;
				$gitVersion = $this->executeShellCommand('git --version');

				switch ($gitVersion) {
					case 'git version 1.7.3.4':
						system('git cherry-pick --strategy=recursive FETCH_HEAD');
						break;

					default:
						system('git cherry-pick --strategy=recursive -X theirs FETCH_HEAD');
						break;
				}
				$cherryPickHash = $this->executeShellCommand('git log --format="%H" -n1 HEAD');
				$this->storePickedCommitHash($commit, $cherryPickHash);
			}
		}
	}

	public function storePickedCommitHash($revisionHash, $cherryPickHash) {
		$file = $this->baseDir . '/beard.lock';
		$commits = $this->getStoredCommits();

		$commits[$revisionHash] = $cherryPickHash;

		file_put_contents($file, json_encode($commits, JSON_PRETTY_PRINT));
	}

	public function isCommitAlreadyPicked($revisionHash) {
		$commits = $this->getStoredCommits();

		if (isset($commits[$revisionHash]) === FALSE){
			return FALSE;
		}

		$commitLog = $this->executeShellCommand('git log -n30');
		return stristr($commitLog, 'commit ' . $commits[$revisionHash]) !== FALSE;
	}

	public function getStoredCommits() {
		$file = $this->baseDir . '/beard.lock';

		$commits = array();
		if (is_file($file)) {
			$commits = json_decode(file_get_contents($file), TRUE);
		}
		if (!is_array($commits)) {
			$commits = array();
		}
		return $commits;
	}

	/**
	 * @param \stdClass $change
	 * @return void
	 */
	public function applyDiffChange($change) {
		$file = $this->baseDir . '/' . $change->file;
		if (!file_exists($this->baseDir . '/' . $change->file)) {
			$this->output->write('<error>The file ' . $change->file . ' doesn\'t exist!</error>' . chr(10));
		}
		$output = $this->executeShellCommand('git apply ' . $file . ' --verbose');
		echo $output;
	}

	/**
	 * @param \stdClass $change
	 * @return void
	 */
	public function applyGithubChange($change) {
		if (isset($change->commit)) {
			$commits = array(
				array(
					'repository' => $change->repository,
					'sha' => $change->commit
				)
			);
		} elseif ($change->pull_request) {
			$commits = $this->getPullRequestCommits($change->repository, $change->pull_request);
		}

		foreach ($commits as $commit) {
			$repositoryUri = 'https://github.com/' . $commit['repository'] . '.git';
			$sha = $commit['sha'];
			if ($this->isCommitAlreadyPicked($sha) === TRUE) {
				$this->output->write('<comment>Already picked</comment>' . chr(10));
			} else {
				$command = 'git fetch --quiet '. $repositoryUri . ' ' . $sha . '';
				$output = $this->executeShellCommand($command);

				echo $output;
				$gitVersion = $this->getGitVersion();

				if ($gitVersion < 1.8) {
					system('git cherry-pick --strategy=recursive FETCH_HEAD');
				} else {
					system('git cherry-pick --strategy=recursive -X theirs FETCH_HEAD');
				}

				$cherryPickHash = $this->executeShellCommand('git log --format="%H" -n1 HEAD');
				$this->storePickedCommitHash($sha, $cherryPickHash);
			}
		}
	}

	public function getGitVersion() {
		$gitVersion = $this->executeShellCommand('git --version');
		preg_match('/[(0-9)\.]+/', $gitVersion, $match);
		$versionString = current($match);
		$parts = explode('.', $versionString);
		return $parts[0] + ($parts[1] / 10);
	}

	public function getPullRequestCommits($repository, $pullRequest) {
		$commitsUri = 'https://api.github.com/repos/' . $repository . '/pulls/' . $pullRequest . '/commits';

		$headers = array(
			'Accept' => 'application/vnd.github.v3+json'
		);

		$caCertTemp = tempnam(sys_get_temp_dir(), 'BeardCaCert');
		$options = array(
			'verify' => $caCertTemp
		);

		if (strlen(\Phar::running()) > 0) {
			$caCertSource = \Phar::running() . '/vendor/rmccue/requests/library/Requests/Transport/cacert.pem';
		} else {
			$caCertSource = BEARD_ROOT_DIR . '/../../../vendor/rmccue/requests/library/Requests/Transport/cacert.pem';
		}
		copy($caCertSource, $caCertTemp);

		$request = \Requests::get($commitsUri, $headers, $options);
		$pullRequestCommits = json_decode($request->body);

		$pullRequestUri = 'https://api.github.com/repos/' . $repository . '/pulls/' . $pullRequest;
		$request = \Requests::get($pullRequestUri, $headers, $options);
		$pullRequest = json_decode($request->body);

		$pullRequestRepositoryUri = $pullRequest->head->repo->full_name;
		$commits = array();
		foreach ($pullRequestCommits as $pullRequestCommit) {
			$commits[] = array(
				'repository' => $pullRequestRepositoryUri,
				'sha' => $pullRequestCommit->sha
			);
		}
		return $commits;
	}

	/**
	 * @param string $command
	 * @return string
	 */
	public function executeShellCommand($command) {
		$output = '';
		$fp = popen($command, 'r');
		while (($line = fgets($fp)) !== FALSE) {
			$output .= $line;
		}
		pclose($fp);
		return trim($output);
	}

	/**
	 * @param \stdClass $change The change object
	 * @return mixed
	 */
	public function fetchChangeInformation($change) {
		$output = file_get_contents($change->gerrit_api_endpoint . 'changes/?q=' . intval($change->change_id) . '&o=CURRENT_REVISION');

		// Remove first line
		$output = substr($output, strpos($output, "\n") + 1);
		// trim []
		$output = ltrim($output, '[');
		$output = rtrim(rtrim($output), ']');

		$data = json_decode($output);
		return $data;
	}

	/**
	 * @param \stdClass $change The change object
	 * @return mixed
	 */
	public function fetchTopicChanges($change) {
		$branch = isset($change->branch) ? $change->branch : 'master';
		$output = file_get_contents($change->gerrit_api_endpoint . 'changes/?q=status:open+topic:' . $change->topic . '+branch:' . $branch);

		// Remove first line
		$output = substr($output, strpos($output, "\n") + 1);

		$data = json_decode($output);
		return $data;
	}

}

?>
