<?php
namespace Famelo\Beard\Systems;

use Famelo\Beard\Interfaces\SystemSettingsInterface;

/**
 *
 */
class WordpressSettings implements SystemSettingsInterface {

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
		'wp-content/uploads'
	);

	/**
	 * @var array
	 */
	protected $temporaryTables = array();

	public function __construct($context = 'Development') {
		if (!file_exists('wp-config.php')) {
			return;
		}

		define('SHORTINIT', TRUE);
		require('wp-config.php');

		$this->host = DB_HOST;
		$this->database = DB_NAME;
		$this->username = DB_USER;
		$this->password = DB_PASSWORD;
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

}
