<?php
namespace Famelo\Beard\Scaffold\Builder\Typo3;

use Famelo\Beard\Scaffold\Builder\Typo3\ExtLocalconfBuilder;
use Famelo\Beard\Scaffold\Builder\Typo3\ExtTablesBuilder;
use Famelo\Beard\Utility\Path;
use Famelo\Beard\Utility\String;


/**
 */
class PluginBuilder {

	const TEMPLATE_CONFIGURE_PLUGIN = '
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	\'--company--.\' . $_EXTKEY,
	\'--name--\',
	--cachedControllers--,
	// non-cacheable actions
	--uncachedControllers--
);
	';

	const TEMPLATE_REGISTER_PLUGIN = '
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	\'--name--\',
	\'--title--\'
);
	';

	/**
	 * @var string
	 */
	public $company;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var array
	 */
	public $cachedControllers = array();

	/**
	 * @var array
	 */
	public $uncachedControllers = array();

	/**
	 * @var string
	 */
	public $defaultController;

	/**
	 * @var string
	 */
	public $defaultAction;

	/**
	 * @var string
	 */
	protected $configurationCode;

	/**
	 * @var string
	 */
	protected $registrationCode;

	/**
	 * @var string
	 */
	protected $oldName;

	/**
	 * @var string
	 */
	protected $basepath;

	public function __construct($name, $basepath = NULL) {
		$extLocalconfBuilder = new ExtLocalconfBuilder(Path::joinPaths($basepath, 'ext_localconf.php'));
		$pluginConfiguration = $extLocalconfBuilder->getPlugin($name);
		if ($pluginConfiguration !== NULL) {
			$this->company = $pluginConfiguration['company'];
			$this->name = $pluginConfiguration['name'];
			$this->oldName = $pluginConfiguration['name'];
			$this->cachedControllers = $pluginConfiguration['cachedControllers'];
			foreach ($this->cachedControllers as $controllerName => $actions) {
				$this->cachedControllers[$controllerName] = explode(',', $actions);
			}
			$this->uncachedControllers = $pluginConfiguration['uncachedControllers'];
			foreach ($this->uncachedControllers as $controllerName => $actions) {
				$this->uncachedControllers[$controllerName] = explode(',', $actions);
			}
			$this->configurationCode = $pluginConfiguration['code'];

			if (count($this->cachedControllers) > 0) {
				$this->defaultController = String::cutSuffix(key($this->cachedControllers), 'Controller');
				$this->defaultAction = reset($this->cachedControllers[$this->defaultController . 'Controller']);
			}
		}

		$extTablesBuilder = new ExtTablesBuilder(Path::joinPaths($basepath, 'ext_tables.php'));
		if ($extTablesBuilder !== NULL) {
			$pluginRegistration = $extTablesBuilder->getPlugin($name);
			$this->title = $pluginRegistration['title'];
			$this->registrationCode = $pluginRegistration['code'];
		}

		$this->basepath = $basepath;
	}

	/**
	 */
	public function save() {
		$cachedControllers = array();
		foreach ($this->cachedControllers as $controllerName => $actions) {
			$cachedControllers[String::addSuffix($controllerName, 'Controller')] = implode(',', $actions);
		}

		$uncachedControllers = array();
		foreach ($this->uncachedControllers as $controllerName => $actions) {
			$uncachedControllers[String::addSuffix($controllerName, 'Controller')] = implode(',', $actions);
		}

		$arguments = array(
			'company' => $this->company,
			'name' => $this->name,
			'title' => $this->title,
			'cachedControllers' => trim(String::prefixLinesWith(var_export($cachedControllers, TRUE), "\t"), "\t"),
			'uncachedControllers' => trim(String::prefixLinesWith(var_export($uncachedControllers, TRUE), "\t"), "\t")
		);

		$extLocalconfBuilder = new ExtLocalconfBuilder(Path::joinPaths($this->basepath, 'ext_localconf.php'));
		if ($this->oldName !== NULL) {
			$extLocalconfBuilder->updateCode($this->configurationCode, $this->renderCode($arguments, self::TEMPLATE_CONFIGURE_PLUGIN));
		} else {
			$extLocalconfBuilder->addCode($this->renderCode($arguments, self::TEMPLATE_CONFIGURE_PLUGIN));
		}
		$extLocalconfBuilder->save();

		$extTablesBuilder = new ExtTablesBuilder(Path::joinPaths($this->basepath, 'ext_tables.php'));
		if ($this->oldName !== NULL) {
			$extTablesBuilder->updateCode($this->registrationCode, $this->renderCode($arguments, self::TEMPLATE_REGISTER_PLUGIN));
		} else {
			$extTablesBuilder->addCode($this->renderCode($arguments, self::TEMPLATE_REGISTER_PLUGIN));
		}
		$extTablesBuilder->save();
	}

	public function renderCode($arguments, $template) {
		$code = trim($template, chr(10));
		foreach ($arguments as $key => $value) {
			$code = str_replace('--' . $key . '--', $value, $code);
		}
		return $code;
	}

	public function remove() {
		$extLocalconfBuilder = new ExtLocalconfBuilder(Path::joinPaths($this->basepath, 'ext_localconf.php'));
		$extLocalconfBuilder->removeCode($this->configurationCode);
		$extLocalconfBuilder->save();

		$extTablesBuilder = new ExtTablesBuilder(Path::joinPaths($this->basepath, 'ext_tables.php'));
		$extTablesBuilder->removeCode($this->registrationCode);
		$extTablesBuilder->save();
	}

	public function addAction($controllerName, $action, $uncached = FALSE) {
		$controllerName = String::addSuffix($controllerName, 'Controller');
		$action = String::cutSuffix($action, 'Action');
		$actions = array();
		if (isset($this->cachedControllers[$controllerName])) {
			$actions = $this->cachedControllers[$controllerName];
		}
		$actions[] = $action;
		$this->cachedControllers[$controllerName] = $actions;

		if ($uncached === FALSE) {
			return;
		}

		$actions = array();
		if (isset($this->uncachedControllers[$controllerName])) {
			$actions = $this->uncachedControllers[$controllerName];
		}
		$actions[] = $action;
		$this->uncachedControllers[$controllerName] = $actions;
	}

	public function setDefaultAction($controllerName, $action) {
		$controllerName = String::addSuffix($controllerName, 'Controller');

		$controllerItem = $this->cachedControllers[$controllerName];
		unset($this->cachedControllers[$controllerName]);
		$this->cachedControllers = array_merge(
			array($controllerName => $controllerItem),
			$this->cachedControllers
		);


		$actionIndex = array_search($action, $this->cachedControllers[$controllerName]);
		unset($this->cachedControllers[$controllerName][$actionIndex]);
		array_unshift(
			$this->cachedControllers[$controllerName],
			$action
		);
	}
}

?>
