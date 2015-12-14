<?php
namespace Famelo\Beard\Interfaces\Cache;

/**
 *
 */
interface WarmupInterface {

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function warmup($output);

	/**
	 * @return boolean
	 */
	public function canWarmup();

}
