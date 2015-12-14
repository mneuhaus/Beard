<?php

namespace Famelo\Beard\Command\SystemImplementations;

use KevinGH\Amend;
use Famelo\Beard\Command;
use Famelo\Beard\Helper;
use Famelo\Beard\Interfaces\Cache\ClearInterface;
use Famelo\Beard\Interfaces\Cache\WarmupInterface;
use Famelo\Beard\Interfaces\Backup\UserdataInterface;
use Symfony\Component\Console\Application as Base;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Famelo\Beard\SystemSettings\Typo3Settings;

/**
 * Clear TYPO3 Caches
 */
class Typo3 implements ClearInterface, UserdataInterface {

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function clear($output) {
		$output->writeln('clearing TYPO3 caches');
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
			$output->writeln('cleared ' . $fileCount . ' files in typo3temp');
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
					$output->writeln('cleared ' . $total . ' rows in the table ' . $tableName);
				}
			}
		}
	}

	/**
	 * @return boolean
	 */
	public function canClear() {
		return file_exists('typo3conf/LocalConfiguration.php');
	}

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function backupUserdata($output) {
		$settings = Typo3Settings::load();
		var_dump($settings);
	}

	/**
	 * @return boolean
	 */
	public function canBackupUserdata() {
		return $this->canClear();
	}
}

?>
