<?php
namespace Famelo\Beard\Interfaces\Backup;

/**
 *
 */
interface UserdataInterface {

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function backupUserdata($output);

	/**
	 * @return boolean
	 */
	public function canBackupUserdata();

}
