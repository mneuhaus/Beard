<?php
namespace Famelo\Beard\Systems;

use Famelo\Beard\Interfaces\SystemSettingsInterface;
use Famelo\Beard\Interfaces\Cache\ClearInterface;
use Symfony\Component\Finder\Finder;

/**
 *
 */
class Typo3System implements SystemSettingsInterface, ClearInterface {

	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var string
	 */
	protected $database;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $databaseType = 'mysql';

	/**
	 * @var array
	 */
	protected $userdataPaths = array(
		'fileadmin',
		'uploads'
	);

	/**
	 * @var array
	 */
	protected $temporaryTables = array(
		'cf_*',
		'cache_*'
	);


	public function __construct($context = 'Development') {
		if (!file_exists('typo3conf/LocalConfiguration.php')) {
			return;
		}

		$localConfiguration = require('typo3conf/LocalConfiguration.php');
		if (file_exists('typo3conf/AdditionalConfiguration.php')) {
			require('typo3conf/AdditionalConfiguration.php');
			if (isset($GLOBALS['TYPO3_CONF_VARS']['DB'])) {
				$localConfiguration['DB'] = array_replace_recursive($localConfiguration['DB'], $GLOBALS['TYPO3_CONF_VARS']['DB']);
			}
		}

		if (isset($localConfiguration['DB']['Connections']['Default'])) {
			$this->host = $localConfiguration['DB']['Connections']['Default']['host'];
			$this->database = $localConfiguration['DB']['Connections']['Default']['dbname'];
			$this->username = $localConfiguration['DB']['Connections']['Default']['user'];
			$this->password = $localConfiguration['DB']['Connections']['Default']['password'];
		} else {
			$this->host = $localConfiguration['DB']['host'];
			$this->database = $localConfiguration['DB']['database'];
			$this->username = $localConfiguration['DB']['username'];
			$this->password = $localConfiguration['DB']['password'];
		}
	}

	public static function load($context = 'Development') {
		if (!file_exists('typo3conf/LocalConfiguration.php')) {
			return FALSE;
		}

		return new Typo3Settings($context);;
	}

	public function getHost() {
		return $this->host;
	}

	public function getDatabase() {
		return $this->database;
	}

	public function getUsername() {
		return $this->username;
	}

	public function getPassword() {
		return $this->password;
	}

	public function getDatabaseType() {
		return $this->databaseType;
	}

	public function getUserdataPaths() {
		return $this->userdataPaths;
	}

	public function getTemporaryTables() {
		return $this->temporaryTables;
	}

	public function isTemporaryTable($tableName) {
		foreach ($this->temporaryTables as $key => $pattern) {
			$patterns[$key] = str_replace('\*', '.*', preg_quote($pattern));
		}
		$compiledPattern = '~^(?:' . implode(' | ', $patterns) . ')~x';

		return (preg_match($compiledPattern, $tableName) > 0);
	}

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function clear($output) {
		$output->writeln('clearing TYPO3 caches');

		$finder = new Finder();
		$finder->files()->in('typo3temp');

		$fileCount = 0;
		foreach ($finder as $file) {
			$filePath = 'typo3temp/' . $file->getRelativePathname();
			unlink($filePath);
			$fileCount++;
		}
		if ($fileCount > 0) {
			$output->writeln('cleared ' . $fileCount . ' files in typo3temp');
		}

		$db = new \mysqli(
			$this->host,
			$this->username,
			$this->password,
			$this->database
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
					$output->writeln('cleared ' . $total . ' rows in the table ' . $tableName);
				}
			}
		}
	}

	/**
	 * @return boolean
	 */
	public function canClear() {
		return !empty($this->database);
	}
}
