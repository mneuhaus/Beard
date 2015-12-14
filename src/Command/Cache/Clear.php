<?php
namespace Famelo\Beard\Command\Cache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Mia3\Koseki\ClassRegister;

/**
 *
 */
class Clear extends Command {

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
			$implementation = new $implementationClassName();
			if ($implementation->canClear()) {
				$implementation->clear($output);
			}
		}
	}
}
