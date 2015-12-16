<?php
namespace Famelo\Beard\Interfaces;

/**
 *
 */
interface SystemSettingsInterface {

	public function getHost();
	public function getDatabase();
	public function getUsername();
	public function getPassword();
	public function getDatabaseType();
	public function getUserdataPaths();
	public function getTemporaryTables();

}
