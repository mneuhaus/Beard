<?php

namespace Famelo\Beard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reset command
 *
 */
class Reset extends Command {

	/**
	 * The output handler.
	 *
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('reset');
		$this->setDescription('Reset all repositories beneath this directory, removing any unpushed changes and applied patches');
		$this->addOption('noConfirmation', null, InputOption::VALUE_NONE, 'Skip the safety question');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;

		if ($input->getOption('noConfirmation') === FALSE) {
			$confirmation = $this->getHelperSet()->get('dialog')->ask(
				$output,
				'<error>Are you sure? This will reset all repositories to its tracked remote repository, removing any non-pushed local changes. Please confirm with "YES":</error> ',
				'NO'
			);
			if (strtolower($confirmation) !== 'yes') {
				$output->writeln('<comment>Aborting...</comment>');
				return;
			}
		}

		$baseDir = getcwd();
		exec(sprintf('find %s -name ".git"', $baseDir), $gitWorkingCopies, $status);

		foreach ($gitWorkingCopies as $gitWorkingCopy) {
			$output = NULL;
			chdir(dirname($gitWorkingCopy));
			$cmd = sprintf('git status', dirname($gitWorkingCopy));
			exec($cmd, $output, $return);
			$path = str_replace($baseDir . '/', '', dirname($gitWorkingCopy));

			if ($output[1] === 'nothing to commit (working directory clean)') {
				if ($this->output->isVerbose()) {
					$this->output->writeln('<info>' . $path . ' is clean</info>');
				}
			} else {
				$cmd = sprintf('git branch -vv');
				exec($cmd, $output, $return);
				$branches = implode(chr(10), $output);
				preg_match('/\* (.+) [a-z0-9]* \[(([^\]]+)\/[^\]:]+)/', $branches, $match);
				echo $this->executeShellCommand('git fetch ' . $match[3]) . chr(10);
				echo $this->executeShellCommand('git reset --hard ' . $match[2]) . chr(10);
			}
			chdir($baseDir);
		}
	}


	public function executeShellCommand($command) {
		$output = '';
		$fp = popen($command, 'r');
		while (($line = fgets($fp)) !== FALSE) {
			$output .= $line;
		}
		pclose($fp);
		return trim($output);
	}

}

?>