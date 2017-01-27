<?php
namespace Famelo\Beard\Scaffold\Typo3;

use Famelo\Beard\Scaffold\Core\Packages\AbstractPackage;
use Famelo\Beard\Scaffold\Core\Packages\PackageInterface;
use Famelo\Beard\Scaffold\Typo3\Components\MetadataComponent;
use Famelo\Beard\Utility\Path;
use Famelo\Beard\Utility\StringUtility;
use Symfony\Component\Finder\Finder;

/*
 * This file belongs to the package "Famelo Soup".
 * See LICENSE.txt that was shipped with this package.
 */

class ExtensionPackage extends AbstractPackage implements PackageInterface {
	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	protected $components = array(
		array(
			'title' => 'Metadata',
			'className' => '\Famelo\Beard\Scaffold\Typo3\Components\MetadataComponent',
			'multiple' => FALSE,
			'mandatory' => TRUE
		),
		array(
			'title' => 'FluidTYPO3',
			'className' => '\Famelo\Beard\Scaffold\Typo3\Components\FluidTypo3Component'
		),
		array(
			'title' => 'Controller',
			'className' => '\Famelo\Beard\Scaffold\Typo3\Components\ControllerComponent',
			'multiple' => TRUE
		),
		array(
			'title' => 'Plugin',
			'className' => '\Famelo\Beard\Scaffold\Typo3\Components\PluginComponent',
			'multiple' => TRUE
		),
//		array(
//			'title' => 'Models',
//			'className' => '\Famelo\Beard\Scaffold\Typo3\Components\ModelComponent',
//			'multiple' => TRUE
//		)
	);

	public function __construct($path = NULL) {
		$this->path = $path;
	}

	public function getName() {
		$metadata = new MetadataComponent(Path::joinPaths($this->path, 'ext_emconf.php'));
		return $metadata->getTitle() . ' (' . basename($this->path) . ')';
	}

	public function getType() {
		return StringUtility::relativeClass(get_class($this));
	}

	public function getPath() {
		return $this->path;
	}

	public function getComponents() {
		$components = $this->components;

		foreach ($components as $key => $componentConfiguration) {
			if (realpath(WORKING_DIRECTORY) == getcwd()
					&& !isset($componentConfiguration['mandatory'])
					&& $componentConfiguration['mandatory'] !== FALSE) {
				return $components;
			}
			$components[$key]['instances'] = $componentConfiguration['className']::getComponents();
		}
		return $components;
	}

	public function create($fieldValues) {
		foreach ($fieldValues['components'] as $componentData) {
			if ($componentData['_class'] === '\Famelo\Beard\Scaffold\Typo3\Components\MetadataComponent') {
				if (file_exists($componentData['extension_key'])) {
					throw new \Exception('Extension directory already exists! (' . $componentData['extension_key'] . ')');
				} else {
					mkdir($componentData['extension_key']);
				}
				chdir($componentData['extension_key']);
				break;
			}
		}
		foreach ($fieldValues['components'] as $componentData) {
			if (isset($componentData['_arguments'])) {
				$reflection = new \ReflectionClass($componentData['_class']);
				$component = $reflection->newInstanceArgs($componentData['_arguments']);
			} else {
				$component = new $componentData['_class']();
			}
			$component->save($componentData);
		}
	}

	public function saveFields($fieldValues) {
		foreach ($fieldValues['components'] as $componentData) {
			if (isset($componentData['_arguments'])) {
				$reflection = new \ReflectionClass($componentData['_class']);
				$component = $reflection->newInstanceArgs($componentData['_arguments']);
			} else {
				$component = new $componentData['_class']();
			}
			if (isset($componentData['_remove'])) {
				$component->remove($componentData);
			} else {
				$component->save($componentData);
			}
		}
	}
}
