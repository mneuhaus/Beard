<?php
namespace Famelo\Beard\Scaffold\Typo3\Components;

use Famelo\Beard\Scaffold\Builder\Typo3\ModelBuilder;
use Famelo\Beard\Utility\Path;
use Famelo\Beard\Utility\StringUtility;
use Famelo\Beard\Scaffold\Core\Components\AbstractComponent;
use Symfony\Component\Finder\Finder;

/*
 * This file belongs to the package "Famelo Soup".
 * See LICENSE.txt that was shipped with this package.
 */

class ModelComponent extends AbstractComponent {
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var ClassFacade
	 */
	public $facade;

	/**
	 * @var string
	 */
	protected $filepath;

	public function __construct($filepath = NULL) {
		$this->filepath = $filepath;
		$this->facade = new ModelBuilder($filepath);
		$this->name = $this->facade->name;
	}

	public static function getComponents() {
		$finder = new Finder();
		$files = $finder->files()->in('.')->path('Classes/Domain/Model/')->name('*.php');
		$instances = array();
		foreach ($files as $file) {
			$instances[] = new ModelComponent($file->getRealPath());
		}
		return $instances;
	}

	public function getArguments() {
		return array($this->filepath);
	}

	public function getProperties() {
		$properties = array();
		foreach ($this->facade->getProperties() as $property) {
			$properties[$property->getName()] = $property;
		}
		return $properties;
	}

	public function getTypeOptions() {
		return array(
			'string' => 'String',
			'boolean' => 'Boolean'
		);
	}

	public function save($arguments) {
		$this->facade->name = $arguments['name'];

		$composer = new ComposerFacade('composer.json');
		$this->facade->namespace = $composer->getNamespace() . '\\Domain\\Model';

		foreach ($arguments['properties'] as $property => $data) {
			if (isset($data['_remove'])) {
				$this->facade->removeProperty($property);
			} else if (isset($existingProperties[$property])) {
				if ($property !== $data['name']) {
					$this->facade->renameProperty($property, $data['name']);
				}
			} else {
				$this->facade->addProperty($data['name'], NULL, array(
					'propertyType' => $data['type']
				));
			}
		}

		$this->facade->save();
	}
}
