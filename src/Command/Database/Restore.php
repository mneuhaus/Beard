<?php
namespace Famelo\Beard\Command\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;
use Mia3\Koseki\ClassRegister;
use Famelo\Beard\Command\Backup\Database;
use Famelo\Beard\Command\AbstractSettingsCommand;

/**
 *
 */
class Restore extends AbstractSettingsCommand {

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('db:restore');
		$this->setDescription('Restore a previously created snapshot');

		$this->addArgument(
			'file',
			InputArgument::OPTIONAL,
			'filename restore into the database'
		);
	}

	/**
	 * @override
	 */
	public function execute(InputInterface $input, OutputInterface $output) {
		$file = $input->getArgument('file');

		if (empty($file)) {
			$choices = array();
			$finder = new Finder();
	        foreach ($finder->in('snapshots')->name('*.sql') as $file) {
	            $choices[filemtime($file)] = $file->getRelativePathname();
	        }
	        krsort($choices);
	        $helper = $this->getHelper('question');
			$choice = $helper->ask($input, $output, new ChoiceQuestion('Please select a snapshot', array_values($choices)));
			$file = 'snapshots/' . $choice;
		}

		$settings = $this->getSettings($input, $output);

		$clearCommand = new Clear();
		$clearCommand->dropAllTables($settings, $input, $output);

		$this->restore($file, $settings, $input, $output);
	}

	public function restore($file, $settings, $input, $output) {
		$command = array('mysql');
		if (!empty($settings->getHost())) {
			$command[] = '-h"' . $settings->getHost() . '"';
		}
		if (!empty($settings->getUsername())) {
			$command[] = '-u"' . $settings->getUsername() . '"';
		}
		if (!empty($settings->getPassword())) {
			$command[] = '-p"' . $settings->getPassword() . '"';
		}
		if (!empty($settings->getDatabase())) {
			$command[] = '"' . $settings->getDatabase() . '"';
		}
		$command[] = '< "' . $file . '"';

		$process = new Process(implode(' ', $command));
		$process->setTimeout(3600);
		$process->run();
		$output->writeln('restored <fg=cyan;bg=black>' . basename($file) . '</> into <fg=cyan;bg=black>' . $settings->getDatabase() . '</>');
	}
}
