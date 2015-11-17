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
use Symfony\Component\Finder\Finder;

/**
 * Patch command.
 *
 */
class ClearCache extends Command {

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
		$this->setName('clear-cache');
		$this->setDescription('Clear cache of the project in the current directory');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;

		switch (TRUE) {
			case file_exists('typo3conf/LocalConfiguration.php'):
				$this->clearTypo3Cache();
				break;

			default:
				# code...
				break;
		}
	}

	public function clearTypo3Cache() {
		$this->output->writeln('clearing TYPO3 caches');
		$localConfiguration = require_once('typo3conf/LocalConfiguration.php');
		require_once('typo3conf/AdditionalConfiguration.php');
		if (isset($GLOBALS['TYPO3_CONF_VARS']['DB'])) {
			$localConfiguration['DB'] = array_replace_recursive($localConfiguration['DB'], $GLOBALS['TYPO3_CONF_VARS']['DB']);
		}

		$finder = new Finder();
		$finder->files()->in('typo3temp');

		$fileCount = 0;
		foreach ($finder as $file) {
			unlink($file->getRealpath());
			$fileCount++;
		}
		if ($fileCount > 0) {
			$this->output->writeln('cleared ' . $fileCount . ' files in typo3temp');
		}

		$db = new \mysqli(
			$localConfiguration['DB']['host'],
			$localConfiguration['DB']['username'],
			$localConfiguration['DB']['password'],
			$localConfiguration['DB']['database']
		);
		$result = $db->query('SHOW TABLES');
		$regex = '(cf_.*|cache_*)';
		foreach ($result->fetch_all(MYSQLI_ASSOC) as $table) {
			$tableName = current($table);
			preg_match('/' . $regex . '/', $tableName);
			if (preg_match('/' . $regex . '/', $tableName) > 0) {
				$result = $db->query('SELECT count(*) as total FROM ' . $tableName);
				$total = $result->fetch_assoc()['total'];
				if ($total > 0) {
					$db->query('TRUNCATE ' . $tableName);
					$this->output->writeln('cleared ' . $total . ' rows in the table ' . $tableName);
				}
			}
		}
	}
}

?>
