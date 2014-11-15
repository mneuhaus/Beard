<?php
namespace Famelo\Beard\Command;

use Famelo\Beard\PHPParser\Printer\TYPO3;
use Doctrine\Common\Inflector\Inflector;
use PhpParser\BuilderFactory;
use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\Template;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Patch command.
 *
 */
class Lock extends Command {

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
		$this->setName('lock');
		$this->setDescription('Lock package to currently used commit');

		$this->addArgument('package', InputArgument::REQUIRED);

		// $this->addOption('add-getter-setter', FALSE, InputOption::VALUE_NONE,
		// 	'Adds missing getters and setters'
  //       );
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;
		$package = $input->getArgument('package');

		$reference = $this->getPackageReference($package);
		if ($reference === NULL) {
			$output->writeln('Unknown package: <comment>"' . $package . '"</comment>');
			return;
		}
		$composer = json_decode(file_get_contents('composer.json'));
		$composer->require = get_object_vars($composer->require);
		$composer->require[$package] = $reference;
		file_put_contents('composer.json', str_replace('\/', '/', json_encode($composer, JSON_PRETTY_PRINT)));

		$output->writeln('Locked the package <info>"' . $package . '"</info> to the commit <info>"' . $reference . '"</info>');

   }

   public function getPackageReference($packageName) {
		$composerLock = json_decode(file_get_contents('composer.lock'));

		foreach ($composerLock->packages as $package) {
			if ($package->name == $packageName) {
				$version = $package->version;
				$commit = $package->source->reference;
				return $version . '#' . $commit;
			}
		}
   }
}

?>