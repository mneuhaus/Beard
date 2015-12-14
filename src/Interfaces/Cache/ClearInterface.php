<?php
namespace Famelo\Beard\Interfaces\Cache;

/**
 *
 */
interface ClearInterface {

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function clear($output);

	/**
	 * @return boolean
	 */
	public function canClear();

}
