<?php
namespace Famelo\Beard\Scaffold\Builder\Typo3;

use Famelo\Beard\Utility\Path;
use Famelo\Beard\Utility\StringUtility;


/**
 */
class ExtTablesBuilder {

	const PATTERN_PLUGIN_REGISTER = '/.*ExtensionUtility::registerPlugin\(([^;]*);/';

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

	public function __construct($filepath = 'ext_tables.php') {
		$this->filepath = $filepath;

		if (file_exists($this->filepath) === TRUE) {
			$this->filecontent = file_get_contents($this->filepath);
		}

		if (empty($this->filecontent)) {
			$this->filecontent = trim(self::TEMPLATE_FILE, chr(10));
		}
	}

	public function getPlugins($value='') {
		preg_match_all(self::PATTERN_PLUGIN_REGISTER, $this->filecontent, $matches);
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
				'title' => $data[2],
				'code' => $matches[0][$key]
			);
		}
		return $plugins;
	}

	public function getPlugin($name) {
		foreach ($this->getPlugins() as $plugin) {
			if ($plugin['name'] === $name) {
				return $plugin;
			}
		}
	}

	public function getFunctions($pattern, $keyIndex = NULL) {
		preg_match_all($pattern, $this->filecontent, $matches);
		$results = array();
		foreach ($matches[1] as $key => $match) {
			$code = '
				$_EXTKEY = "";
				return array(' . trim($match, '()') . ');
			';
			$data = eval($code);
			if ($keyIndex !== NULL) {
				$results[$data[$keyIndex]] = array(
					'code' => $matches[0][$key],
					'data' => $data
				);
			} else {
				$results[] = array(
					'code' => $matches[0][$key],
					'data' => $data
				);
			}
		}
		return $results;
	}

	public function updateCode($oldCode, $newCode) {
		$this->filecontent = str_replace($oldCode, $newCode, $this->filecontent);
	}

	public function addCode($code) {
		$this->filecontent.= chr(10) . trim($code, chr(10)) . chr(10);
	}

	public function removeCode($code) {
		$this->filecontent = str_replace($code, '', $this->filecontent);
	}

	/**
	 */
	public function save() {
		file_put_contents($this->filepath, trim($this->filecontent));
	}
}

?>
