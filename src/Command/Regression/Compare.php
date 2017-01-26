<?php

namespace Famelo\Beard\Command\Regression;

use Famelo\Beard\Utility\StringUtility;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Patch command.
 *
 */
class Compare extends Command {

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
		$this->setName('regression:compare');
		$this->addArgument('source');
		$this->setDescription('Compare current state against baseline');
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
		if (!file_exists('.beard-regression/comparison')) {
			mkdir('.beard-regression/comparison');
		}
		foreach ($source as $uri) {
			echo $uri . chr(10);
			$fileNameChanged = '.beard-regression/' . StringUtility::slugify($uri) . '.changed.png';
			$fileNameBaseline = '.beard-regression/' . StringUtility::slugify($uri) . '.baseline.png';
			$fileNameComparison = '.beard-regression/comparison/' . StringUtility::slugify($uri) . '.comparison.png';

			$this->executeShellCommand($phantomCommand . ' "' . $uri . '" ' . $fileNameChanged);

			$baselineImage = Image::make($fileNameBaseline);
			$changedImage = Image::make($fileNameChanged);
			$changedImage->resizeCanvas($baselineImage->width(), $baselineImage->height(), 'top-left');
			$changedImage->save();

			$this->executeShellCommand('compare "' . $fileNameBaseline . '" "' . $fileNameChanged . '" "' . $fileNameComparison . '"' );
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