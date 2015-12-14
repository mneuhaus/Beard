<?php
namespace Famelo\Beard\Command\Backup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Mia3\Koseki\ClassRegister;

/**
 *
 */
class Userdata extends Command {

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
		$this->setName('backup:userdata');
		$this->setDescription('Backup userdata of the project in this directory');

		$this->addArgument(
			'file',
			InputArgument::OPTIONAL,
			'filename to safe userdata to'
		);
	}

	/**
	 * @override
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
		$file = $input->getArgument('file') ? $input->getArgument('file') : 'userdata-' . date('d.m.Y-H.i.s');

		if (substr($file, -7) !== '.tar.gz') {
			$file.= '.tar.gz';
		}

		$implementations = ClassRegister::getImplementations('Famelo\Beard\Interfaces\SystemSettingsInterface', !PHAR_MODE);
		$userdataPaths = array();
		foreach ($implementations as $implementationClassName) {
			$settings = new $implementationClassName();
			if (count($settings->getUserdataPaths()) > 0) {
				foreach ($settings->getUserdataPaths() as $userdataPath) {
					if (file_exists($userdataPath)) {
						$userdataPaths[] = $userdataPath;
					}
				}
			}
		}
		$command = array('tar -czf', $file);

		$process = new Process(implode(' ', $command) . ' ' . implode(' ', $userdataPaths));
		$process->setTimeout(3600);
		$process->run();
		$output->writeln('created backing up <fg=cyan;bg=black>' . implode(', ', $userdataPaths) . '</> into <fg=cyan;bg=black>' . $file . '</> (' . number_format(filesize($file) / 1024 / 1024, 2) . 'MB)');
	}
}
