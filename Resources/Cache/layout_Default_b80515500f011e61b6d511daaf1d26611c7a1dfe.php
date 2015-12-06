<?php

class FluidCache_layout_Default_b80515500f011e61b6d511daaf1d26611c7a1dfe extends \TYPO3\Fluid\Core\Compiler\AbstractCompiledTemplate {

public function getVariableContainer() {
	// TODO
	return new \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer();
}
public function getLayoutName(\TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
$self = $this;

return 'Default';
}
public function hasLayout() {
return TRUE;
}

/**
 * Main Render function
 */
public function render(\TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
$self = $this;
$output0 = '';
// Rendering ViewHelper TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper
$arguments1 = array();
// Rendering ViewHelper TYPO3\Fluid\ViewHelpers\LayoutViewHelper
$arguments2 = array();
$arguments2['name'] = 'Default';
$renderChildrenClosure3 = function() use ($renderingContext, $self) {
return NULL;
};
$viewHelper4 = $self->getViewHelper('$viewHelper4', $renderingContext, 'TYPO3\Fluid\ViewHelpers\LayoutViewHelper');
$viewHelper4->setArguments($arguments2);
$viewHelper4->setRenderingContext($renderingContext);
$viewHelper4->setRenderChildrenClosure($renderChildrenClosure3);
// End of ViewHelper TYPO3\Fluid\ViewHelpers\LayoutViewHelper
$arguments1['value'] = $viewHelper4->initializeArgumentsAndRender();
$arguments1['keepQuotes'] = false;
$arguments1['encoding'] = 'UTF-8';
$arguments1['doubleEncode'] = true;
$renderChildrenClosure5 = function() use ($renderingContext, $self) {
return NULL;
};
$value6 = ($arguments1['value'] !== NULL ? $arguments1['value'] : $renderChildrenClosure5());

$output0 .= !is_string($value6) && !(is_object($value6) && method_exists($value6, '__toString')) ? $value6 : htmlspecialchars($value6, ($arguments1['keepQuotes'] ? ENT_NOQUOTES : ENT_COMPAT), $arguments1['encoding'], $arguments1['doubleEncode']);

$output0 .= '

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<base href="http://localhost/expose/web/" />

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<body>

</body>
</html>

';
// Rendering ViewHelper TYPO3\Fluid\ViewHelpers\RenderViewHelper
$arguments7 = array();
$arguments7['section'] = 'Main';
$arguments7['partial'] = NULL;
$arguments7['arguments'] = array (
);
$arguments7['optional'] = false;
$renderChildrenClosure8 = function() use ($renderingContext, $self) {
return NULL;
};
$viewHelper9 = $self->getViewHelper('$viewHelper9', $renderingContext, 'TYPO3\Fluid\ViewHelpers\RenderViewHelper');
$viewHelper9->setArguments($arguments7);
$viewHelper9->setRenderingContext($renderingContext);
$viewHelper9->setRenderChildrenClosure($renderChildrenClosure8);
// End of ViewHelper TYPO3\Fluid\ViewHelpers\RenderViewHelper

$output0 .= $viewHelper9->initializeArgumentsAndRender();

return $output0;
}


}