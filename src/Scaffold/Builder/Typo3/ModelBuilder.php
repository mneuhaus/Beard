<?php
namespace Famelo\Beard\Scaffold\Builder\Typo3;

use Famelo\Beard\Scaffold\Builder\Php\ClassBuilder;
use Famelo\Beard\Utility\Path;
use Famelo\Beard\Utility\StringUtility;
use PhpParser\BuilderFactory;
use PhpParser\ParserFactory;


/**
 */
class ModelBuilder extends ClassBuilder {

	const TEMPLATE_MODEL = '<?php
namespace Foo\Bar\Domain\Model;

/**
 */
class Model extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

}';

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

	public function __construct($filepath) {
		if (file_exists($filepath)) {
			parent::__construct($filepath);
			$this->name = $this->getName();
			$this->namespace = $this->getNamespace();

			foreach ($this->getMethods() as $method) {
				$this->actions[] = StringUtility::cutSuffix($method->getName(), 'Action');
			}
		} else {
			$this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
			$this->factory = new BuilderFactory();
			$this->statements = $this->parser->parse(self::TEMPLATE_MODEL);
		}
	}

	public function hasAction($name) {
		return in_array(StringUtility::cutSuffix($name, 'Action'), $this->actions);
	}

	public function renameAction($oldName, $newName) {
		$this->renameMethod(
				StringUtility::addSuffix($oldName, 'Action'),
				StringUtility::addSuffix($newName, 'Action')
		);
	}

	public function addAction($name) {
		$this->addMethod(StringUtility::addSuffix($name, 'Action'));
	}

	public function removeAction($name) {
		$this->removeMethod(StringUtility::addSuffix($name, 'Action'));
	}

	/**
	 */
	public function save($targetPath = 'Classes/Controller/') {
		$className = ucfirst(StringUtility::addSuffix($this->name, 'Controller'));
		$targetFileName = $targetPath . $className . '.php';
		if ($targetFileName !== $this->filepath) {
			unlink($this->filepath);
		}
		// $composer = new ComposerFacade('composer.json');
		// $namespace = $composer->getNamespace() . '\\Controller';
		parent::setNamespace($this->namespace);
		parent::setClassName($className);
		parent::save($targetFileName);
	}

	public function remove() {
		unlink($this->filepath);
	}
}