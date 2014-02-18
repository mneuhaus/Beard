<?php

namespace Famelo\Beard\Command;

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
				$this->applyChange($change);
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
			case 'patch':
			case 'diff': $this->applyDiffChange($change);
				break;
			default: $this->output->write('woot?');
		}

		chdir($this->baseDir);
		$this->output->writeln('');
	}

	public function applyGerritChange($change) {
		$commits = $this->executeShellCommand('git log -n30');
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
			$ref = $changeInformation->revisions->{$changeInformation->current_revision}->fetch->git->ref;
			if (isset($change->patch_set)) {
				$explodedRef = explode('/', $ref);
				array_pop($explodedRef);
				$explodedRef[] = $change->patch_set;
				$ref = implode('/', $explodedRef);
			}

			$command = 'git fetch --quiet git://' . $change->gerrit_git . '/' . $changeInformation->project . ' ' . $ref . '';
			$output = $this->executeShellCommand($command);

			$commit = $this->executeShellCommand('git log --format="%H" -n1 FETCH_HEAD');
			if (stristr($commits, '(cherry picked from commit ' . $commit . ')') !== FALSE) {
				$this->output->write('<comment>Already picked</comment>' . chr(10));
			} else {
				echo $output;
				$gitVersion = $this->executeShellCommand('git --version');
				switch ($gitVersion) {
					case 'git version 1.7.3.4':
						system('git cherry-pick -x --strategy=recursive FETCH_HEAD');
						break;

					default:
						system('git cherry-pick -x --strategy=recursive -X theirs FETCH_HEAD');
						break;
				}
			}
		}
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

}

?>