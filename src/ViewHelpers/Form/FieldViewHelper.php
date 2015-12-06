<?php
namespace Famelo\Beard\ViewHelpers\Form;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 */
class FieldViewHelper extends AbstractViewHelper {

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
		$this->registerArgument('name', 'string', 'Name of the field', FALSE, NULL);
		$this->registerArgument('control', 'string', 'Specifies the control to use to render this field', FALSE, 'Text');
		$this->registerArgument('value', 'mixed', 'Value of the form field', FALSE, NULL);
		$this->registerArgument('placeholder', 'mixed', 'Placeholder for the input', FALSE, NULL);
		$this->registerArgument('wrap', 'string', 'Specifies the wrap used to render the field', FALSE, 'Default');
		$this->registerArgument('class', 'string', 'control class', FALSE, 'form-control');
		$this->registerArgument('required', 'boolean', 'Specifies, if this form field is required', FALSE, FALSE);
		$this->registerArgument('arguments', 'array', 'additional arguments for the control', FALSE, array());
		$this->registerArgument('label', 'string', 'custom label for the field', FALSE, NULL);
	}

	/**
	 *
	 * @return string Rendered string
	 * @api
	 */
	public function render() {
		if (empty($this->arguments['class'])) {
			$this->arguments['class'] = 'form-control';
		}

		$this->arguments = array_merge($this->arguments, $this->arguments['arguments']);
		unset($this->arguments['arguments']);

		$partial = $this->arguments['control'];

		// if ($this->arguments['required'] === TRUE) {
		// 	$this->arguments['additionalAttributes']['required'] = $this->arguments['required'];
		// 	$this->arguments['requiredString'] = '*';
		// }

		$this->arguments['id'] = str_replace(array('[', ']'), array('-', ''), $this->arguments['name']);

		$control = $this->renderChildren();
		if ($control === NULL) {
			$control = $this->viewHelperVariableContainer->getView()->renderPartial('Form/Field/' . $partial, NULL, $this->arguments);
		}

		if (empty($this->arguments['wrap'])) {
			return $control;
		}

		return $this->viewHelperVariableContainer->getView()->renderPartial('Form/Wrap/' . $this->arguments['wrap'], NULL, array_merge(
			$this->arguments,
			array('control' => $control)
		));
	}
}

?>
