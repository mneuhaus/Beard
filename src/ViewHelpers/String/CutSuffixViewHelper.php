<?php
namespace Famelo\Beard\ViewHelpers\String;

use Famelo\Beard\Utility\StringUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 */
class CutSuffixViewHelper extends AbstractViewHelper {

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('string', 'string', 'String', FALSE, NULL);
		$this->registerArgument('suffix', 'string', 'Suffix');
	}

	/**
	 *
	 * @return string Rendered string
	 * @api
	 */
	public function render() {
		$string = $this->arguments['string'];
		if ($string === NULL) {
			$string = $this->renderChildren();
		}
		return StringUtility::cutSuffix($string, $this->arguments['suffix']);
	}
}

?>
