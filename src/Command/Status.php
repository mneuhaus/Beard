<?php
namespace Famelo\Beard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Status command.
 *
 */
class Status extends Command {

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

		$baseDir = getcwd();

		exec(sprintf('find %s -name ".git"', $baseDir), $gitWorkingCopies, $status);
		foreach ($gitWorkingCopies as $gitWorkingCopy) {
			$output = NULL;
			$path = str_replace($baseDir . '/', '', dirname($gitWorkingCopy));
			if (substr($path, 0, strlen('Data/Surf')) == 'Data/Surf')  {
				continue;
			}
			$cmd = sprintf('cd "%s" && git status', dirname($gitWorkingCopy));
			exec($cmd, $output, $return);

			if (in_array('nothing to commit, working directory clean', $output)) {
				if ($this->output->isVerbose()) {
					$this->output->writeln('<info>' . $path . ' is clean</info>');
				}
			} else {
				if (in_array('Not currently on any branch.', $output)) {
					$this->output->writeln('<error>' . $path . ' is not on a branch and has local changes</error>');
				} elseif (in_array('Changes not staged for commit:', $output)) {
					$this->output->writeln('<error>' . $path . ' has local changes</error>');
				} else {
					if (isset($output[1])) {
						$this->output->writeln('<comment>' . $path . ' ' . $output[1] . '</comment>');
					}
				}

				foreach ($output as $outputLine) {
					if (preg_match('/^#*\t/', $outputLine)) {
						if (strpos($outputLine, ':') === FALSE) {
							$this->output->writeln(str_replace("\t", "\tuntracked:  ", $outputLine));
						} else {
							$this->output->writeln($outputLine);
						}
					}
				}
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