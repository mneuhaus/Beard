<?php

namespace Famelo\Beard\Command;

use Famelo\Beard\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Traversable;

/**
 * Builds a new Phar.
 *
 */
class Status extends Command {
	/**
	 * The Box instance.
	 *
	 * @var Box
	 */
	private $box;

	/**
	 * The configuration settings.
	 *
	 * @var Configuration
	 */
	private $config;

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
		$this->setName('status');
		$this->setDescription('Show the current status of all git repositories inside this directory');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;

		$baseDir = getcwd();

		$clean = TRUE;
		exec(sprintf('find %s -name ".git"', $baseDir), $gitWorkingCopies, $status);
		foreach ($gitWorkingCopies as $gitWorkingCopy) {
			$output = NULL;
			$cmd = sprintf('cd %s && git status', dirname($gitWorkingCopy));
			exec($cmd, $output, $return);

			$path = str_replace($baseDir . '/', '', dirname($gitWorkingCopy));

			if ($output[1] === 'nothing to commit (working directory clean)') {
				if ($verbose === TRUE) {
					$this->output->writeln('<info>' . $path . ' is clean</info>');
				}
			} else {
				$clean = FALSE;

				if ($output[0] === '# Not currently on any branch.') {
					$this->output->writeln('<error>' . $path . ' is not on a branch and has local changes</error>');
				} elseif ($output[1] === '# Changes not staged for commit:') {
					$this->output->writeln('<error>' . $path . ' has local changes</error>');
				} else {
					if ($this->output->isVerbose()) {
						$this->output->writeln('<comment>' . $path . ' ' . $output[1] . '</comment>');
					}
				}

				foreach ($output as $outputLine) {
					if (preg_match('/^#\t/', $outputLine)) {
						if (strpos($outputLine, ':') === FALSE) {
							$this->output->writeln(str_replace("\t", "\tuntracked:  ", $outputLine));
						} else {
							$this->output->writeln($outputLine);
						}
					}
				}

				// chdir($path);
				// $commits = $this->executeShellCommand('git log -n10');
				// if (stristr($commits, '(cherry picked from commit') !== FALSE) {
				// 	var_dump($commits);
				// }
				// chdir($baseDir);
			}
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