<?php
namespace Famelo\Beard\Command\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Mia3\Koseki\ClassRegister;
use Famelo\Beard\Command\Backup\Database;

/**
 *
 */
class Snapshot extends Command {

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('db:snapshot');
		$this->setDescription('Make a snapshot of the database in a directory Snapshots and use db:restore to quickly reset your database');

		$this->addArgument(
			'comment',
			InputArgument::OPTIONAL,
			'comment added to the snapshot'
		);
	}

	/**
	 * @override
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
		$comment = $input->getArgument('comment') ? '-' . $input->getArgument('comment') : '';
		$file = 'snapshots/' . date('d.m.Y-H.i.s') . $comment;

		$databaseCommand = new Database();
		$input = new ArrayInput(array('file' => $file), $databaseCommand->getDefinition());
		$databaseCommand->execute($input, $output);
	}
}
