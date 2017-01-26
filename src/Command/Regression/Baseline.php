<?php

namespace Famelo\Beard\Command\Regression;

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
		// $this->addOption('url', NULL, InputOption::VALUE_OPTIONAL);
		// $this->addOption('list', NULL, InputOption::VALUE_OPTIONAL);
		$this->setDescription('Create baseline to compare against');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$source = $input->getArgument('source');
		if (file_exists($source)) {
			$source = explode(chr(10), file_get_contents($source));
		} elseif (is_string($source)) {
			$source = array($source);
		}
		$phantomJsPath = BOX_PATH . '/Resources/Tools/phantomjs-macosx/';
		$phantomCommand = $phantomJsPath . 'bin/phantomjs ' . $phantomJsPath . 'examples/rasterize.js';
		if (!file_exists('.beard-regression')) {
			mkdir('.beard-regression');
		}
		foreach ($source as $uri) {
			echo $uri . chr(10);
			$fileName = '.beard-regression/' . StringUtility::slugify($uri) . '.baseline.png';
			$this->executeShellCommand($phantomCommand . ' "' . $uri . '" ' . $fileName);
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