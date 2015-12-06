<?php
namespace Famelo\Beard\Scaffold\Core\Vendor;

use Symfony\Component\Finder\Finder;
use Mia3\Koseki\ClassRegister;

/*
 * This file belongs to the package "Famelo Soup".
 * See LICENSE.txt that was shipped with this package.
 */

class VendorManager {
	public function findRelevantVendors($path) {
		$implementations = ClassRegister::getImplementations('Famelo\Beard\Scaffold\Core\Vendor\VendorInterface', !PHAR_MODE);
		$vendors = array();
		foreach ($implementations as $implementation) {
			$vendor = new $implementation(WORKING_DIRECTORY);
			if ($vendor->isRelevantToDirectory()) {
				$vendors[] = $vendor;
			}
		}
		return $vendors;
	}

	public function getPackages($name) {
		$recpieClassName = '\Famelo\Beard\Scaffold\\' . $name;
		return new $recpieClassName;
	}
}
