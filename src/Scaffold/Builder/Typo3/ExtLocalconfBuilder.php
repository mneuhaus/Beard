<?php
namespace Famelo\Beard\Scaffold\Builder\Typo3;

use Famelo\Beard\Utility\Path;


/**
 */
class ExtLocalconfBuilder {

	const PATTERN_PLUGIN_CONFIGURE = '/.*ExtensionUtility::configurePlugin\(([^;]*);/';

	const TEMPLATE_FILE = '
<?php
if (!defined(\'TYPO3_MODE\')) {
	die(\'Access denied.\');
}
	';

	/**
	 * @var string
	 */
	protected $filepath;

	/**
	 * @var string
	 */
	protected $filecontent;

	/**
	 * load information from file
	 *
	 * @param string $filepath
	 */
	public function __construct($filepath = 'ext_localconf.php') {
		$this->filepath = $filepath;

		if (file_exists($this->filepath) === TRUE) {
			$this->filecontent = file_get_contents($this->filepath);
		}

		if (empty($this->filecontent)) {
			$this->filecontent = trim(self::TEMPLATE_FILE, chr(10));
		}
	}

	/**
	 * get all defined plugins
	 *
	 * @param string $value
	 * @return array
	 */
	public function getPlugins($value='') {
		preg_match_all(static::PATTERN_PLUGIN_CONFIGURE, $this->filecontent, $matches);
		$plugins = array();
		foreach ($matches[1] as $key => $match) {
			$code = '
				$_EXTKEY = "";
				return array(' . trim($match, '()') . ');
			';
			$data = eval($code);
			$plugins[] = array(
				'company' => trim($data[0], '.'),
				'name' => $data[1],
				'cachedControllers' => $data[2],
				'uncachedControllers' => $data[3],
				'code' => $matches[0][$key]
			);
		}
		return $plugins;
	}

	/**
	 * get one specific plugin
	 *
	 * @param string $name
	 * @return array
	 */
	public function getPlugin($name) {
		foreach ($this->getPlugins() as $plugin) {
			if ($plugin['name'] === $name) {
				return $plugin;
			}
		}
	}

	/**
	 * update a specific part of code
	 * @param string $oldCode
	 * @param string $newCode
	 * @return void
	 */
	public function updateCode($oldCode, $newCode) {
		$this->filecontent = str_replace($oldCode, $newCode, $this->filecontent);
	}

	/**
	 * add new code
	 *
	 * @param string $code
	 */
	public function addCode($code) {
		$this->filecontent.= chr(10) . chr(10) . trim($code, chr(10)) . chr(10);
	}

	/**
	 * remove part of code
	 *
	 * @param string $code
	 * @return void
	 */
	public function removeCode($code) {
		$this->filecontent = str_replace($code, '', $this->filecontent);
	}

	/**
	 * save this file
	 */
	public function save() {
		file_put_contents($this->filepath, $this->filecontent);
	}
}

?>
