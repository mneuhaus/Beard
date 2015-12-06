<?php
namespace Famelo\Beard\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 */
class ClassIdViewHelper extends AbstractViewHelper {

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
		$this->registerArgument('className', 'string', 'Classname', FALSE, NULL);
	}

	/**
	 *
	 * @return string Rendered string
	 * @api
	 */
	public function render() {
		$className = $this->arguments['className'];
		if ($className === NULL) {
			$className = $this->renderChildren();
		}
		return trim(str_replace(array('\Famelo\Beard\Scaffold', '\\'), array('', '-'), $className), '/');
	}
}

?>
