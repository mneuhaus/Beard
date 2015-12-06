<?php
namespace Famelo\Beard\Scaffold\Builder\Php\Reflection;


/**
 */
class ReflectionMethod {

	/**
	 * @var \PhpParser\Node\Stmt\ClassMethod
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
		return $this->statement->name;
	}
}

?>
