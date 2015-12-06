<?php
namespace Famelo\Beard\Scaffold\Core;

use Famelo\Beard\Scaffold\Core\Vendor\VendorManager;
use Famelo\Beard\Utility\String;
use TYPO3Fluid\Fluid\View\TemplateView;

/*
 * This file belongs to the package "Famelo Soup".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class WebController
 * @package Famelo\Beard\Scaffold\Core
 */
class WebController {

	/**
	 * @param string $arguments
	 */
	public function index($arguments) {
		$vendorManager = new VendorManager();
		$vendors = $vendorManager->findRelevantVendors(WORKING_DIRECTORY);
		$this->render('index', array(
			'vendors' => $vendors
		));
	}

	/**
	 * @param $arguments
	 */
	public function newPackage($arguments) {
		$packageClassName = String::classNameFromPath($arguments['package']);
		$package = new $packageClassName();

		$this->render(str_replace('.', '/', String::cutSuffix($arguments['package'], 'Package')) . '/New', array(
			'package' => $package
		));
	}

	public function createPackage($arguments) {
		$packageClassName = String::classNameFromPath($arguments['package']);
		$package = new $packageClassName();
		$package->create($_POST);

		$this->redirect('');
	}

	public function editPackage($arguments) {
		$packageClassName = String::classNameFromPath($arguments['package']);
		$package = new $packageClassName();
		chdir($arguments['path']);

		$this->render(str_replace('.', '/', String::cutSuffix($arguments['package'], 'Package')) . '/Edit', array(
			'package' => $package
		));
	}

	public function savePackage($arguments) {
		$packageClassName = String::classNameFromPath($arguments['package']);
		$package = new $packageClassName();
		chdir($arguments['path']);
		$package->saveFields($_POST);

		$this->redirect('package/' . $arguments['package'] . '/' . $arguments['path']);
	}

	public function redirect($path) {
		header('Location: /' . $path);
		exit;
	}

	public function render($template, $variables = array()) {
		$paths = new \TYPO3Fluid\Fluid\View\TemplatePaths();
		// $paths->setTemplateRootPaths(array(__DIR__ . '/../Templates/'));
		$paths->setLayoutRootPaths(array(BASE_DIRECTORY . '/../../Resources/Layouts/'));
		$paths->setPartialRootPaths(array(BASE_DIRECTORY . '/../../Resources/Partials/'));

		$parts = explode('/', $template);
		array_walk($parts, function(&$value, $key){
			$value = ucfirst($value);
		});
		$path = implode('/', $parts);
		$templateFile = BASE_DIRECTORY . '/../../Resources/Templates/' . $path . '.html';
		$paths->setTemplatePathAndFilename($templateFile);

		$view = new TemplateView($paths);
		$view->assignMultiple($variables);

		$view->getViewHelperResolver()->registerNamespace('s', 'Famelo\\Beard\\ViewHelpers');

		echo $view->render();
	}
}
