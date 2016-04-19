<?php
namespace Famelo\Beard\Command\Cache;

use Famelo\Beard\Command\AbstractSettingsCommand;
use Mia3\Koseki\ClassRegister;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class Clear extends AbstractSettingsCommand {

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
		$this->setName('cache:clear');
		$this->setDescription('Clear cache of the project in the current directory');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;

		$implementations = ClassRegister::getImplementations('Famelo\Beard\Interfaces\Cache\ClearInterface', !PHAR_MODE);
		foreach ($implementations as $implementationClassName) {
			$implementation = new $implementationClassName($input->getOption('context'));
			if ($implementation->canClear()) {
				$implementation->clear($output);
			}
		}

		if (function_exists('apc_clear_cache')) {
			apc_clear_cache();
			apc_clear_cache('user');
			$output->writeln('cleared apc cache');
		}

		if (function_exists('apcu_clear_cache')) {
			apcu_clear_cache();
			$output->writeln('cleared apcu cache');
		}

		if (function_exists('opcache_reset')) {
			opcache_reset();
			$output->writeln('cleared opcache cache');
		}
	}
}
