<?php

namespace Famelo\Beard\Command\SystemImplementations;

use KevinGH\Amend;
use Famelo\Beard\Command;
use Famelo\Beard\Helper;
use Famelo\Beard\Interfaces\Cache\ClearInterface;
use Famelo\Beard\Interfaces\Cache\WarmupInterface;
use Symfony\Component\Console\Application as Base;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Clear TYPO3 Caches
 */
class Flow implements ClearInterface, WarmupInterface {

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function clear($output) {
		$output->writeln('clearing Flow caches');
		system('./flow flow:cache:flush --force');
	}

	/**
	 * @return boolean
	 */
	public function canClear() {
		return file_exists('flow') && file_exists('Packages/Framework/TYPO3.Flow');
	}

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function warmup($output) {
		$output->writeln('warming up Flow caches');
		system('./flow flow:cache:warmup');
	}

	/**
	 * @return boolean
	 */
	public function canWarmup() {
		return $this->canClear();
	}
}

?>
