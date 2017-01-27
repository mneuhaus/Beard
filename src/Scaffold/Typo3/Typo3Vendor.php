<?php
namespace Famelo\Beard\Scaffold\TYPO3;

use Famelo\Beard\Scaffold\Core\Vendor\VendorInterface;
use Famelo\Beard\Scaffold\Typo3\ExtensionPackage;
use Famelo\Beard\Utility\StringUtility;
use Famelo\Beard\Utility\Path;
use Symfony\Component\Finder\Finder;

/*
 * This file belongs to the package "Famelo Beard".
 * See LICENSE.txt that was shipped with this package.
 */

class Typo3Vendor implements VendorInterface {
	/**
	 * @var string
	 */
	public $name = 'TYPO3 Extensions';

	public function isRelevantToDirectory() {
		$finder = new Finder();
		foreach ($finder->directories()->in(getcwd())->depth('== 0') as $directory) {
			if (file_exists($directory->getRealPath() . '/ext_emconf.php')) {
				return TRUE;
			}
		}
		return FALSE;
	}

	public function getPackages() {
		$finder = new Finder();
		$recipies = array();
		foreach ($finder->directories()->in(getcwd())->depth('== 0') as $directory) {
			if (file_exists($directory->getRealPath() . '/ext_emconf.php')) {
				$recipies[] = new ExtensionPackage(Path::relativePath($directory->getRealPath()));
			}
		}
		return $recipies;
	}

	public function getPackageType() {
		return StringUtility::relativeClass('\Famelo\Beard\Scaffold\Typo3\ExtensionPackage');
	}
}
