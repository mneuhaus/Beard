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
class Warmup extends AbstractSettingsCommand {

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
		$this->setName('cache:warmup');
		$this->setDescription('Warmup the caches for the project in the current directory');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;

		$implementations = ClassRegister::getImplementations('Famelo\Beard\Interfaces\Cache\WarmupInterface', !PHAR_MODE);
		foreach ($implementations as $implementationClassName) {
			$implementation = new $implementationClassName($input->getOption('context'));
			if ($implementation->canWarmup()) {
				$implementation->warmup($output);
			}
		}
	}
}
