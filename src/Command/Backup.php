<?php
namespace Famelo\Beard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Mia3\Koseki\ClassRegister;
use Famelo\Beard\Command\Backup\Database;
use Famelo\Beard\Command\Backup\Userdata;

/**
 *
 */
class Backup extends Command {

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
		$this->setName('backup');
		$this->setAliases(array(
			'backup:all'
		));
		$this->setDescription('Backup database and userdata of the project in this directory');

		$this->addArgument(
			'file',
			InputArgument::OPTIONAL,
			'filename to safe the backups to'
		);
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		if (empty($input->getArgument('file'))) {
			$input->setArgument('file', 'backup-' . date('d.m.Y-H.i.s'));
		}

		$databaseCommand = new Database();
		$databaseCommand->execute($input, $output);

		$userdata = new Userdata();
		$userdata->execute($input, $output);
	}
}
