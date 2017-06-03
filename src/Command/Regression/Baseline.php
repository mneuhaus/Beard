<?php

namespace Famelo\Beard\Command\Regression;

use Famelo\Beard\Process\ProcessPool;
use Famelo\Beard\Utility\StringUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Patch command.
 *
 */
class Baseline extends Command {

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
		$this->setName('regression:baseline');
		$this->addArgument('source');
        $this->addOption('threads', 't', InputOption::VALUE_OPTIONAL,
            'Number of Threads used if source is a file with urls (default: 8)', 8);
		$this->setDescription('Create baseline to compare against');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
        $this->output = $output;
        $source = $input->getArgument('source');
        if (file_exists($source)) {
            $sources = explode(chr(10), file_get_contents($source));
            $pool = new ProcessPool($input->getOption('threads'));
            $scriptPath = $_SERVER['SCRIPT_NAME'];
            foreach ($sources as $uri) {
                $pool->call($scriptPath . ' regression:baseline "' . $uri . '"');
            }
            $pool->run();
        } elseif (is_string($source)) {
            $start = microtime(true);
            $phantomJsPath = BOX_PATH . '/Resources/Tools/phantomjs-macosx/';
            $phantomCommand = $phantomJsPath . 'bin/phantomjs ' . $phantomJsPath . 'examples/rasterize.js';
            if (!file_exists('.beard-regression')) {
                mkdir('.beard-regression');
            }
            $fileName = '.beard-regression/' . StringUtility::slugify($source) . '.baseline.png';
            $this->executeShellCommand($phantomCommand . ' "' . $source . '" ' . $fileName);
            $output->writeln($source . ' (' . number_format(microtime(true) - $start) . 's)');
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