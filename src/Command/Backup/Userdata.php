<?php
namespace Famelo\Beard\Command\Backup;

use Famelo\Beard\Command\AbstractSettingsCommand;
use Mia3\Koseki\ClassRegister;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 *
 */
class Userdata extends AbstractSettingsCommand {

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
			$settings = new $implementationClassName($input->getOption('context'));
			if (count($settings->getUserdataPaths()) > 0) {
				foreach ($settings->getUserdataPaths() as $userdataPath) {
					if (file_exists($userdataPath)) {
						$userdataPaths[] = '"' . $userdataPath . '"';
					}
				}
			}
		}
		if (file_exists($input->getArgument('file') . '.sql')) {
			$userdataPaths[] = $input->getArgument('file') . '.sql';
		}
		$command = array('tar --dereference -czf', $file);

		$process = new Process(implode(' ', $command) . ' ' . implode(' ', $userdataPaths));
		$process->setTimeout(3600);
		$process->run();
		$output->writeln('created backup of <fg=cyan;bg=black>' . implode(', ', $userdataPaths) . '</> into <fg=cyan;bg=black>' . $file . '</> (' . number_format(filesize($file) / 1024 / 1024, 2) . 'MB)');
	}
}
