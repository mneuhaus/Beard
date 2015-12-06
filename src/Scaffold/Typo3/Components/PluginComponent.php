<?php
namespace Famelo\Beard\Scaffold\Typo3\Components;

use Famelo\Beard\Scaffold\Builder\Typo3\ExtLocalconfBuilder;
use Famelo\Beard\Scaffold\Builder\Typo3\PluginBuilder;
use Famelo\Beard\Utility\Path;
use Famelo\Beard\Utility\String;
use Famelo\Beard\Scaffold\Core\Components\AbstractComponent;
use Symfony\Component\Finder\Finder;

/*
 * This file belongs to the package "Famelo Soup".
 * See LICENSE.txt that was shipped with this package.
 */

class PluginComponent extends AbstractComponent {
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var PluginFacade
	 */
	public $facade;

	public function __construct($name = NULL) {
		$this->name = $name;
		$this->facade = new PluginBuilder($name);
	}

	public static function getComponents() {
		$facade = new ExtLocalconfBuilder();
		$instances = array();
		foreach ($facade->getPlugins() as $plugin) {
			$instances[] = new PluginComponent($plugin['name']);
		}
		return $instances;
	}

	public function getArguments() {
		return array($this->name);
	}

	public function getFilepath() {
		return $this->filepath;
	}

	public function getCachedControllers() {
		return $this->facade->cachedControllers;
	}

	public function getCachedControllersValue() {
		$value = array();
		foreach ($this->facade->cachedControllers as $controller => $actions) {
			foreach ($actions as $action) {
				$value[] = String::cutSuffix($controller, 'Controller') . ':' . String::cutSuffix($action, 'Action');
			}
		}
		return implode(',', $value);
	}

	public function getUncachedControllers() {
		return $this->facade->uncachedControllers;
	}

	public function getUncachedControllersValue() {
		$value = array();
		foreach ($this->facade->uncachedControllers as $controller => $actions) {
			foreach ($actions as $action) {
				$value[] = String::cutSuffix($controller, 'Controller') . ':' . String::cutSuffix($action, 'Action');
			}
		}
		return implode(',', $value);
	}

	public function getName() {
		return $this->facade->name;
	}

	public function getTitle() {
		return $this->facade->title;
	}

	public function remove($arguments) {
		$this->facade->remove();
	}

	public function save($arguments) {
		$this->facade->name = $arguments['name'];
		$this->facade->title = $arguments['title'];
		$this->facade->title = $arguments['title'];

		$this->facade->cachedControllers = array();
		$this->facade->uncachedControllers = array();
		foreach ($arguments['cachedActions'] as $cachedAction) {
			$parts = explode(':', $cachedAction);
			$this->facade->addAction($parts[0], $parts[1], in_array($cachedAction, $arguments['uncachedActions']));
		}

		$parts = explode(':', $arguments['defaultAction']);
		$this->facade->setDefaultAction($parts[0], $parts[1]);

		$this->facade->save();
	}
}
