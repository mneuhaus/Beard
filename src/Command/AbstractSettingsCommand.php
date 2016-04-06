<?php
namespace Famelo\Beard\Command;

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
class AbstractSettingsCommand extends Command {

    /**
     * @override
     */
    protected function configure()
    {
        parent::configure();

		$this->addOption('context', 'c', InputOption::VALUE_OPTIONAL, 'Context used to locate settings', 'Development');
    }

	public function getSettings($input, $output) {
		$implementations = ClassRegister::getImplementations('Famelo\Beard\Interfaces\SystemSettingsInterface', !PHAR_MODE);
		$userdataPaths = array();
		foreach ($implementations as $implementationClassName) {
			$settings = new $implementationClassName($input->getOption('context'));
			if (!empty($settings->getDatabase())) {
				$output->writeln('found settings based on <fg=yellow;bg=black>' . $implementationClassName . '</>');
				return $settings;
			}
		}
	}

}
