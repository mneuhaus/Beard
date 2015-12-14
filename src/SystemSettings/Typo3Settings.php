<?php
namespace Famelo\Beard\SystemSettings;

use Famelo\Beard\Interfaces\SystemSettingsInterface;

/**
 *
 */
class Typo3Settings implements SystemSettingsInterface {

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

		$this->host = $localConfiguration['DB']['host'];
		$this->database = $localConfiguration['DB']['database'];
		$this->username = $localConfiguration['DB']['username'];
		$this->password = $localConfiguration['DB']['password'];
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

}
