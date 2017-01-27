<?php
namespace Famelo\Beard\Scaffold\Builder\Typo3;

use Famelo\Beard\Scaffold\Builder\Php\ClassBuilder;
use Famelo\Beard\Utility\Path;
use Famelo\Beard\Utility\StringUtility;
use PhpParser\BuilderFactory;
use PhpParser\ParserFactory;


/**
 */
class ControllerBuilder extends ClassBuilder {

	const TEMPLATE_CONTROLLER = '<?php
namespace Foo\Bar\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2014 Marc Neuhaus <mneuhaus@famelo.com>, Famelo
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * FooController
 */
class FooController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

}
	';

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $namespace;

	/**
	 * @var string
	 */
	public $actions = array();

	public function __construct($filepath = NULL) {
		if ($filepath !== NULL && file_exists($filepath)) {
			parent::__construct($filepath);
			$this->name = $this->getName();
			$this->namespace = $this->getNamespace();

			foreach ($this->getMethods() as $method) {
				$this->actions[] = StringUtility::cutSuffix($method->getName(), 'Action');
			}
		} else {
			$this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
			$this->factory = new BuilderFactory;
			$this->statements = $this->parser->parse(self::TEMPLATE_CONTROLLER);
		}
	}

	/**
	 * check if the controller contains a specific Action
	 *
	 * @param  string  $name
	 * @return boolean
	 */
	public function hasAction($name) {
		return in_array(StringUtility::cutSuffix($name, 'Action'), $this->actions);
	}

	/**
	 * rename an action
	 *
	 * @param  string $oldName
	 * @param  string $newName
	 * @return void
	 */
	public function renameAction($oldName, $newName) {
		$this->renameMethod(
			StringUtility::addSuffix($oldName, 'Action'),
			StringUtility::addSuffix($newName, 'Action')
		);
	}

	/**
	 * add a new action
	 *
	 * @param sring $name
	 * @return void
	 */
	public function addAction($name) {
		$this->addMethod(StringUtility::addSuffix($name, 'Action'));
	}

	/**
	 * remove a specific action
	 *
	 * @param sring $name
	 * @return void
	 */
	public function removeAction($name) {
		$this->removeMethod(StringUtility::addSuffix($name, 'Action'));
	}

	/**
	 * save the controller
	 *
	 * @param string $targetPath
	 */
	public function save($targetPath = 'Classes/Controller/') {
		$className = ucfirst(StringUtility::addSuffix($this->name, 'Controller'));
		$targetFileName = $targetPath . $className . '.php';
		if ($targetFileName !== $this->filepath && file_exists($this->filepath)) {
			unlink($this->filepath);
		}
		// $composer = new ComposerFacade('composer.json');
		// $namespace = $composer->getNamespace() . '\\Controller';
		parent::setNamespace($this->namespace);
		parent::setClassName($className);
		parent::save($targetFileName);
	}

	/**
	 * remove this controller
	 *
	 * @return void
	 */
	public function remove() {
		unlink($this->filepath);
	}
}

?>
