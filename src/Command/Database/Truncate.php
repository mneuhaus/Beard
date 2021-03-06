<?php
namespace Famelo\Beard\Command\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;
use Mia3\Koseki\ClassRegister;
use Famelo\Beard\Command\Backup\Database;

/**
 *
 */
class Truncate extends Database {

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('db:truncate');
		$this->setDescription('Truncate all database tables of this project');
	}

	/**
	 * @override
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
		$implementations = ClassRegister::getImplementations('Famelo\Beard\Interfaces\SystemSettingsInterface', !PHAR_MODE);

		$settings = $this->getSettings($input, $output);

		if ($settings === NULL) {
			$output->writeln('could not find any project settings');
			return;
		}

		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion('Are you sure, you want to truncate all tables in the database <fg=cyan;bg=black>' . $settings->getDatabase() . '</>? [yes/no] ' . chr(10), false);

		if (!$helper->ask($input, $output, $question)) {
			return;
		}

		$this->truncateTables($settings, $input, $output);
	}

	public function truncateTables($settings, InputInterface $input, OutputInterface $output) {
		$connection = new \mysqli(
			$settings->getHost(),
			$settings->getUsername(),
			$settings->getPassword(),
			$settings->getDatabase()
		);

		$result = $connection->query('SHOW TABLES');
		if ($result === FALSE) {
			return;
		}
		$rows = $result->fetch_all();
		$connection->query('SET foreign_key_checks = 0;');
		if ($output->isVerbose()) {
			$output->writeln('Query: SET foreign_key_checks = 0;');
		}

		foreach ($rows as $row) {
			$connection->query("TRUNCATE TABLE " . $row[0]);
			if ($output->isVerbose()) {
				$output->writeln("TRUNCATE TABLE " . $row[0]);
			}
		}

		if ($output->isVerbose()) {
			$output->writeln('SET foreign_key_checks = 1;');
		}
		$connection->query('SET foreign_key_checks = 1;');

		$output->writeln('truncated <fg=yellow;bg=black>' . count($rows) . '</> tables in <fg=cyan;bg=black>' . $settings->getDatabase() . '</>');
	}
}
