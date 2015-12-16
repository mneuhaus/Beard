<?php
namespace Famelo\Beard\Command\Backup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Mia3\Koseki\ClassRegister;
use Famelo\Beard\Command\AbstractSettingsCommand;

/**
 *
 */
class Database extends AbstractSettingsCommand {

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('backup:db');
		$this->setDescription('Backup database of the project in this directory');

		$this->addArgument(
			'file',
			InputArgument::OPTIONAL,
			'filename to safe database to'
		);
	}

	/**
	 * @override
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
		$file = $input->getArgument('file') ? $input->getArgument('file') : 'database-' . date('d.m.Y-H.i.s');

		if (substr($file, -7) !== '.sql') {
			$file.= '.sql';
		}

		$implementations = ClassRegister::getImplementations('Famelo\Beard\Interfaces\SystemSettingsInterface', !PHAR_MODE);

		$settings = $this->getSettings($input, $output);

		if ($settings === NULL) {
			$output->writeln('could not find any project settings');
			return;
		}

		$this->truncateTemporaryTables($settings, $input, $output);

		$command = array('mysqldump');
		if (!empty($settings->getHost())) {
			$command[] = '-h' . $settings->getHost();
		}
		if (!empty($settings->getUsername())) {
			$command[] = '-u' . $settings->getUsername();
		}
		if (!empty($settings->getPassword())) {
			$command[] = '-p' . $settings->getPassword();
		}
		if (!empty($settings->getDatabase())) {
			$command[] = $settings->getDatabase();
		}
		$command[] = '> "' . $file . '"';

		if (!file_exists(dirname($file))) {
			mkdir(dirname($file), 0775, TRUE);
		}

		$process = new Process(implode(' ', $command));
		$process->setTimeout(3600);
		$process->run();
		$output->writeln('created backup up of <fg=cyan;bg=black>' . $settings->getDatabase() . '</> into <fg=cyan;bg=black>' . $file . '</> (' . number_format(filesize($file) / 1024 / 1024, 2) . 'MB)');
	}

	public function truncateTemporaryTables($settings, $input, $output) {
		$connection = new \mysqli(
			$settings->getHost(),
			$settings->getUsername(),
			$settings->getPassword(),
			$settings->getDatabase()
		);

		$patterns = $settings->getTemporaryTables();
		if (empty($patterns)) {
			return;
		}
		foreach ($patterns as $key => $pattern) {
			$patterns[$key] = str_replace('\*', '.*', preg_quote($pattern));
		}
		$compiledPattern = '~^(?:' . implode(' | ', $patterns) . ')~x';

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
			if (preg_match($compiledPattern, $row[0]) !== 1) {
				continue;
			}
			$connection->query("TRUNCATE TABLE " . $row[0]);
			if ($output->isVerbose()) {
				$output->writeln("TRUNCATE TABLE " . $row[0]);
			}
		}

		$connection->query('SET foreign_key_checks = 0;');
		if ($output->isVerbose()) {
			$output->writeln('Query: SET foreign_key_checks = 0;');
		}
	}

}
