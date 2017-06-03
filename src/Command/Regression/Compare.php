<?php

namespace Famelo\Beard\Command\Regression;

use Famelo\Beard\Process\ProcessPool;
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
class Compare extends Command
{

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
    protected function configure()
    {
        parent::configure();
        $this->setName('regression:compare');
        $this->addArgument('source');
        $this->addOption('threads', 't', InputOption::VALUE_OPTIONAL,
            'Number of Threads used if source is a file with urls (default: 8)', 8);
        $this->setDescription('Compare current state against baseline');
    }

    /**
     * @override
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $source = $input->getArgument('source');
        if (file_exists($source)) {
            $sources = explode(chr(10), file_get_contents($source));
            $pool = new ProcessPool($input->getOption('threads'));
            $scriptPath = $_SERVER['SCRIPT_NAME'];
            foreach ($sources as $uri) {
                $pool->call($scriptPath . ' regression:compare "' . $uri . '"');
            }
            $pool->run();
        } elseif (is_string($source)) {
            $start = microtime(true);
            $phantomJsPath = BOX_PATH . '/Resources/Tools/phantomjs-macosx/';
            $phantomCommand = $phantomJsPath . 'bin/phantomjs ' . $phantomJsPath . 'examples/rasterize.js';
            if (!file_exists('.beard-regression')) {
                mkdir('.beard-regression');
            }
            if (!file_exists('.beard-regression/comparison')) {
                mkdir('.beard-regression/comparison');
            }

            $fileNameChanged = '.beard-regression/' . StringUtility::slugify($source) . '.changed.png';
            $fileNameBaseline = '.beard-regression/' . StringUtility::slugify($source) . '.baseline.png';
            $fileNameComparison = '.beard-regression/comparison/' . StringUtility::slugify($source) . '.comparison.png';

            $this->executeShellCommand($phantomCommand . ' "' . $source . '" ' . $fileNameChanged);

            $baselineImage = Image::make($fileNameBaseline);
            $changedImage = Image::make($fileNameChanged);
            $changedImage->resizeCanvas($baselineImage->width(), $baselineImage->height(), 'top-left');
            $changedImage->save();

            $this->executeShellCommand('compare "' . $fileNameBaseline . '" "' . $fileNameChanged . '" "' . $fileNameComparison . '"');

            $output->writeln($source . ' (' . number_format(microtime(true) - $start) . 's)');
        }
    }

    public function executeShellCommand($command)
    {
        $output = '';
        $fp = popen($command, 'r');
        while (($line = fgets($fp)) !== false) {
            $output .= $line;
        }
        pclose($fp);

        return trim($output);
    }
}