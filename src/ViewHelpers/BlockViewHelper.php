<?php
namespace Famelo\Beard\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 * @api
 */
class BlockViewHelper extends AbstractViewHelper {

	/**
	 * @var boolean
	 */
	protected $escapeOutput = FALSE;

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('partial', 'string', 'Partial to render, with or without section');
		$this->registerArgument('arguments', 'array', 'Array of variables to be transferred. Use {_all} for all variables', FALSE, array());
	}

	/**
	 * Renders the content.
	 *
	 * @return string
	 * @api
	 */
	public function render() {
		$partial = $this->arguments['partial'];
		$arguments = (array) $this->arguments['arguments'];
		$arguments['content'] = $this->renderChildren();
		return $this->viewHelperVariableContainer->getView()->renderPartial($partial, NULL, $arguments);
	}

	/**
	 * Handles additional arguments, sorting out any data-
	 * prefixed tag attributes and assigning them. Then passes
	 * the unassigned arguments to the parent class' method,
	 * which in the default implementation will throw an error
	 * about "undeclared argument used".
	 *
	 * @param array $arguments
	 * @return void
	 */
	public function handleAdditionalArguments(array $arguments) {
		$this->arguments['arguments'] = array_replace(
			$this->arguments['arguments'],
			$arguments
		);
	}

}
