<?php
namespace Famelo\Beard\Scaffold\Typo3\Components;

use Famelo\Beard\Scaffold\Builder\Typo3\ExtEmconfBuilder;
use Famelo\Beard\Scaffold\Builder\Typo3\ExtTablesBuilder;
use Famelo\Beard\Utility\Path;
use Famelo\Beard\Utility\StringUtility;
use Famelo\Beard\Scaffold\Core\Components\AbstractComponent;
use Symfony\Component\Finder\Finder;

/*
 * This file belongs to the package "Famelo Soup".
 * See LICENSE.txt that was shipped with this package.
 */

class FluidTypo3Component extends AbstractComponent {

	const PATTERN_PROVIDER_REGISTER = '/.*Core::registerProviderExtensionKey\(([^;]*);/';

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var array
	 */
	public $fluidpages = array();

	/**
	 * @var array
	 */
	public $fluidcontent = array();

	/**
	 * @var array
	 */
	public $fluidbackend = array();

	/**
	 * @var array
	 */
	public $vhsActive = FALSE;

	/**
	 * @var array
	 */
	public $providers = FALSE;

	/**
	 * @var string
	 */
	protected $composer = array(
		"autoload" => array(
			"psr-4" => array(
			)
		)
	);

	/**
	 * @var string
	 */
	protected $filepath;

	/**
	 * @var string
	 */
	protected $extensionKey;

	/**
	 * @var array
	 */
	static protected $paths = array(
		'Classes/Controller/'
	);

	public function __construct($filepath = NULL) {
		$this->extTablesFacade = new ExtTablesBuilder();
		$this->providers = $this->extTablesFacade->getFunctions(self::PATTERN_PROVIDER_REGISTER, 1);

		$providers = array(
			'Page' => 'fluidpages',
			'Content' => 'fluidcontent',
			'Backend' => 'fluidbackend'
		);
		foreach ($providers as $providerName => $providerExtension) {
			$templatePath = Path::joinPaths( 'Resources/Private/Templates', $providerName);
			if (!file_exists($templatePath)) {
				continue;
			}
			$files = scandir($templatePath);
			foreach ($files as $file) {
				if (substr($file, 0, 1) == '.') {
					continue;
				}
				array_push($this->$providerExtension, basename($file, '.html'));
			}
		}
	}

	public static function getComponents() {
		$fluidTypo3 = new FluidTypo3Component();
		if (count($fluidTypo3->fluidpages) == 0 && count($fluidTypo3->fluidcontent) == 0 && count($fluidTypo3->fluidbackend) ==  0) {
			return array();
		}
		return array($fluidTypo3);
	}

	public function getArguments() {
		return array($this->filepath);
	}

	public function getTitle() {
		return $this->data['title'];
	}

	public function getFilepath() {
		return $this->filepath;
	}

	public function save($fieldValues) {
		$extEmconfFacade = new ExtEmconfBuilder();

		$providers = array(
			'Page' => 'fluidpages',
			'Content' => 'fluidcontent',
			'Backend' => 'fluidbackend'
		);

		foreach ($providers as $providerName => $providerExtension) {
			if (isset($fieldValues[$providerExtension])) {
				if (!isset($this->providers[$providerName])) {
					$this->extTablesFacade->addCode("\FluidTYPO3\Flux\Core::registerProviderExtensionKey('Foo.FooBuilder', '" . $providerName . "');");
				}
				$extEmconfFacade->addDependency($providerExtension);
				$this->addTemplates($fieldValues[$providerExtension], $providerName);
			} else {
				if (isset($this->providers[$providerName])) {
					$this->extTablesFacade->removeCode($this->providers[$providerName]['code']);
				}
				$extEmconfFacade->removeDependency($providerExtension);
			}
		}

		$this->extTablesFacade->save();
		$extEmconfFacade->save();
	}

	public function addTemplates($templates, $providerName) {
		foreach ($templates as $templateName => $template) {
			$oldTemplatePath = Path::joinPaths( 'Resources/Private/Templates', $providerName, $templateName . '.html');
			if (isset($template['_remove'])) {
				unlink($oldTemplatePath);
				continue;
			}

			$templatePath = Path::joinPaths( 'Resources/Private/Templates', $providerName, ucfirst($template['name']) . '.html');
			if (file_exists($templatePath)) {
				continue;
			}
			if (!file_exists(dirname($templatePath))) {
				mkdir(dirname($templatePath), 0775, TRUE);
			}

			if (file_exists($oldTemplatePath)) {
				rename($oldTemplatePath, $templatePath);
			} else {
				file_put_contents($templatePath, 'foo');
			}
		}
	}

	public function remove() {
		$extEmconfFacade = new ExtEmconfBuilder();

		$providers = array(
			'Page' => 'fluidpages',
			'Content' => 'fluidcontent',
			'Backend' => 'fluidbackend'
		);

		foreach ($providers as $providerName => $providerExtension) {
			if (isset($this->providers[$providerName])) {
				$this->extTablesFacade->removeCode($this->providers[$providerName]['code']);
			}
			$extEmconfFacade->removeDependency($providerExtension);

			$templatePath = Path::joinPaths( 'Resources/Private/Templates', $providerName);
			if (!file_exists($templatePath)) {
				continue;
			}
			$files = scandir($templatePath);
			foreach ($files as $file) {
				if (substr($file, 0, 1) == '.') {
					continue;
				}
				unlink(Path::joinPaths($templatePath, $file));
			}
			rmdir($templatePath);
		}

		$this->extTablesFacade->save();
		$extEmconfFacade->save();
	}

}
