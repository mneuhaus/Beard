<?php
namespace Famelo\Beard\Scaffold\Builder\Php\Reflection;


/**
 */
class ReflectionProperty {

	/**
	 * @var \PhpParser\Node\Stmt\Property
	 */
	protected $statement;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $class;

	public function __construct($statement, $class) {
		$this->statement = $statement;
		$this->class = $class;
	}

	public function getName() {
		$propertyProperty = $this->statement->props[0];
		return $propertyProperty->name;
	}

	public function getDocComment() {
		return $this->statement->getDocComment();
	}
}

?>
