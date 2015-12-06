<?php
namespace Famelo\Beard\ViewHelpers\Form;

use Famelo\Beard\Utility\String;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 */
class NameViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('prefix', 'string', 'Set Prefix', FALSE, NULL);
		$this->registerArgument('name', 'string', 'name to prefix', FALSE, NULL);
	}

	/**
	 *
	 * @return string Rendered string
	 * @api
	 */
	public function render() {
		if ($this->arguments['prefix'] !== NULL) {
			$this->pushPrefix($this->arguments['prefix']);
			$output = $this->renderChildren();
			$this->popPrefix();
			return $output;
		}

		$name = $this->getPrefix();
		if ($this->arguments['name'] !== NULL) {
			$name.= '.' . $this->arguments['name'];
		}
		return String::pathToformName($name);
	}

	public function pushPrefix($prefix) {
		$stack = $this->viewHelperVariableContainer->get(static::class, 'stack', array());
		$stack[] = $prefix;
		$this->viewHelperVariableContainer->addOrUpdate(static::class, 'stack', $stack);
	}

	public function getPrefix() {
		$stack = $this->viewHelperVariableContainer->get(static::class, 'stack', array());
		return end($stack);
	}

	public function popPrefix() {
		$stack = $this->viewHelperVariableContainer->get(static::class, 'stack', array());
		array_pop($stack);
		$this->viewHelperVariableContainer->addOrUpdate(static::class, 'stack', $stack);
	}
}

?>
