<?php

class FluidCache_partial_Table_Layout_4fc5d82fd5d1496205545621abf939c6b4a843ea extends \TYPO3\Fluid\Core\Compiler\AbstractCompiledTemplate {

public function getVariableContainer() {
	// TODO
	return new \TYPO3\Fluid\Core\ViewHelper\TemplateVariableContainer();
}
public function getLayoutName(\TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
$self = $this;

return NULL;
}
public function hasLayout() {
return FALSE;
}

/**
 * Main Render function
 */
public function render(\TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
$self = $this;
$output0 = '';

$output0 .= '

';
// Rendering ViewHelper Flowpack\Expose\ViewHelpers\BehaviorViewHelper
$arguments1 = array();
$arguments1['objects'] = \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode::getPropertyPath($renderingContext->getTemplateVariableContainer(), 'items', $renderingContext);
$arguments1['behaviors'] = \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode::getPropertyPath($renderingContext->getTemplateVariableContainer(), 'schema.listBehaviors', $renderingContext);
$renderChildrenClosure2 = function() use ($renderingContext, $self) {
$output3 = '';

$output3 .= '
	<div class="row">
		<div class="col-xs-12">
			<div class="row expose-toolbar">
				<div class="col-xs-4">
				</div>
				<div class="col-xs-8">
					';
// Rendering ViewHelper TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper
$arguments4 = array();
// Rendering ViewHelper Flowpack\Expose\ViewHelpers\BlockViewHelper
$arguments5 = array();
$arguments5['name'] = 'top';
$renderChildrenClosure6 = function() use ($renderingContext, $self) {
return NULL;
};
$viewHelper7 = $self->getViewHelper('$viewHelper7', $renderingContext, 'Flowpack\Expose\ViewHelpers\BlockViewHelper');
$viewHelper7->setArguments($arguments5);
$viewHelper7->setRenderingContext($renderingContext);
$viewHelper7->setRenderChildrenClosure($renderChildrenClosure6);
// End of ViewHelper Flowpack\Expose\ViewHelpers\BlockViewHelper
$arguments4['value'] = $viewHelper7->initializeArgumentsAndRender();
$arguments4['keepQuotes'] = false;
$arguments4['encoding'] = 'UTF-8';
$arguments4['doubleEncode'] = true;
$renderChildrenClosure8 = function() use ($renderingContext, $self) {
return NULL;
};
$value9 = ($arguments4['value'] !== NULL ? $arguments4['value'] : $renderChildrenClosure8());

$output3 .= !is_string($value9) && !(is_object($value9) && method_exists($value9, '__toString')) ? $value9 : htmlspecialchars($value9, ($arguments4['keepQuotes'] ? ENT_NOQUOTES : ENT_COMPAT), $arguments4['encoding'], $arguments4['doubleEncode']);

$output3 .= '
				</div>
			</div>
			<table class="table table-bordered table-striped expose-table">
				';
// Rendering ViewHelper TYPO3\Fluid\ViewHelpers\RenderViewHelper
$arguments10 = array();
$arguments10['partial'] = 'Table/Header';
// Rendering Array
$array11 = array();
$array11['items'] = \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode::getPropertyPath($renderingContext->getTemplateVariableContainer(), 'items', $renderingContext);
$array11['schema'] = \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode::getPropertyPath($renderingContext->getTemplateVariableContainer(), 'schema', $renderingContext);
$arguments10['arguments'] = $array11;
$arguments10['section'] = NULL;
$arguments10['optional'] = false;
$renderChildrenClosure12 = function() use ($renderingContext, $self) {
return NULL;
};
$viewHelper13 = $self->getViewHelper('$viewHelper13', $renderingContext, 'TYPO3\Fluid\ViewHelpers\RenderViewHelper');
$viewHelper13->setArguments($arguments10);
$viewHelper13->setRenderingContext($renderingContext);
$viewHelper13->setRenderChildrenClosure($renderChildrenClosure12);
// End of ViewHelper TYPO3\Fluid\ViewHelpers\RenderViewHelper

$output3 .= $viewHelper13->initializeArgumentsAndRender();

$output3 .= '
				';
// Rendering ViewHelper TYPO3\Fluid\ViewHelpers\RenderViewHelper
$arguments14 = array();
$arguments14['partial'] = 'Table/Body';
// Rendering Array
$array15 = array();
$array15['items'] = \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode::getPropertyPath($renderingContext->getTemplateVariableContainer(), 'items', $renderingContext);
$array15['schema'] = \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode::getPropertyPath($renderingContext->getTemplateVariableContainer(), 'schema', $renderingContext);
$arguments14['arguments'] = $array15;
$arguments14['section'] = NULL;
$arguments14['optional'] = false;
$renderChildrenClosure16 = function() use ($renderingContext, $self) {
return NULL;
};
$viewHelper17 = $self->getViewHelper('$viewHelper17', $renderingContext, 'TYPO3\Fluid\ViewHelpers\RenderViewHelper');
$viewHelper17->setArguments($arguments14);
$viewHelper17->setRenderingContext($renderingContext);
$viewHelper17->setRenderChildrenClosure($renderChildrenClosure16);
// End of ViewHelper TYPO3\Fluid\ViewHelpers\RenderViewHelper

$output3 .= $viewHelper17->initializeArgumentsAndRender();

$output3 .= '
			</table>
			';
// Rendering ViewHelper TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper
$arguments18 = array();
// Rendering ViewHelper Flowpack\Expose\ViewHelpers\BlockViewHelper
$arguments19 = array();
$arguments19['name'] = 'bottom';
$renderChildrenClosure20 = function() use ($renderingContext, $self) {
return NULL;
};
$viewHelper21 = $self->getViewHelper('$viewHelper21', $renderingContext, 'Flowpack\Expose\ViewHelpers\BlockViewHelper');
$viewHelper21->setArguments($arguments19);
$viewHelper21->setRenderingContext($renderingContext);
$viewHelper21->setRenderChildrenClosure($renderChildrenClosure20);
// End of ViewHelper Flowpack\Expose\ViewHelpers\BlockViewHelper
$arguments18['value'] = $viewHelper21->initializeArgumentsAndRender();
$arguments18['keepQuotes'] = false;
$arguments18['encoding'] = 'UTF-8';
$arguments18['doubleEncode'] = true;
$renderChildrenClosure22 = function() use ($renderingContext, $self) {
return NULL;
};
$value23 = ($arguments18['value'] !== NULL ? $arguments18['value'] : $renderChildrenClosure22());

$output3 .= !is_string($value23) && !(is_object($value23) && method_exists($value23, '__toString')) ? $value23 : htmlspecialchars($value23, ($arguments18['keepQuotes'] ? ENT_NOQUOTES : ENT_COMPAT), $arguments18['encoding'], $arguments18['doubleEncode']);

$output3 .= '
		</div>
		';
// Rendering ViewHelper TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper
$arguments24 = array();
// Rendering ViewHelper Flowpack\Expose\ViewHelpers\BlockViewHelper
$arguments25 = array();
$arguments25['name'] = 'sidebar';
$renderChildrenClosure26 = function() use ($renderingContext, $self) {
return NULL;
};
$viewHelper27 = $self->getViewHelper('$viewHelper27', $renderingContext, 'Flowpack\Expose\ViewHelpers\BlockViewHelper');
$viewHelper27->setArguments($arguments25);
$viewHelper27->setRenderingContext($renderingContext);
$viewHelper27->setRenderChildrenClosure($renderChildrenClosure26);
// End of ViewHelper Flowpack\Expose\ViewHelpers\BlockViewHelper
$arguments24['value'] = $viewHelper27->initializeArgumentsAndRender();
$arguments24['keepQuotes'] = false;
$arguments24['encoding'] = 'UTF-8';
$arguments24['doubleEncode'] = true;
$renderChildrenClosure28 = function() use ($renderingContext, $self) {
return NULL;
};
$value29 = ($arguments24['value'] !== NULL ? $arguments24['value'] : $renderChildrenClosure28());

$output3 .= !is_string($value29) && !(is_object($value29) && method_exists($value29, '__toString')) ? $value29 : htmlspecialchars($value29, ($arguments24['keepQuotes'] ? ENT_NOQUOTES : ENT_COMPAT), $arguments24['encoding'], $arguments24['doubleEncode']);

$output3 .= '
	</div>
';
return $output3;
};
$viewHelper30 = $self->getViewHelper('$viewHelper30', $renderingContext, 'Flowpack\Expose\ViewHelpers\BehaviorViewHelper');
$viewHelper30->setArguments($arguments1);
$viewHelper30->setRenderingContext($renderingContext);
$viewHelper30->setRenderChildrenClosure($renderChildrenClosure2);
// End of ViewHelper Flowpack\Expose\ViewHelpers\BehaviorViewHelper

$output0 .= $viewHelper30->initializeArgumentsAndRender();

return $output0;
}


}