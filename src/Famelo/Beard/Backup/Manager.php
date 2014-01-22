<?php

namespace Famelo\Beard\Backup;

use Alchemy\Zippy\Zippy;
use Famelo\Beard\Backup\Sources\FilesSource;
use Famelo\Beard\Backup\Sources\WordpressSource;
use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Filesystem;


/**
 */
class Manager {
	/**
	 * @var array
	 */
	protected $sources;

	/**
	 * @var array
	 */
	protected $destinations = array();

	/**
	 * @var string
	 */
	protected $tmpPath;

	/**
	 * @var string
	 */
	protected $compression;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var integer
	 */
	protected $days = 30;

	/**
	 * @var array
	 */
	protected $gatheredFiles = array();

	public function __construct($config = NULL, $output = NULL) {
		$this->output = $output;
		if (is_array($config)) {
			if (isset($config['sources'])) {
				foreach($config['sources'] as $name => $source) {
					$this->sources[$name] = $source;
				}
			}

			if (isset($config['compression'])) {
				$this->compression = $config['compression'];
			}

			$this->name = $config['name'];

			if (isset($config['retentionDays'])) {
				$this->days = $config['retentionDays'];
			}

			if (isset($config['destinations'])) {
				$this->destinations = $config['destinations'];
			}
		}

		$this->tmpPath = path(getcwd(), '.tmp' . substr(md5(uniqid(mt_rand(), true)), 0, 5), $config['name'] . '-' . date('d-m-Y--H-i-s'));

		define('BACKUP_TEMP_PATH', dirname($this->tmpPath));
	}

	public function run() {
		mkdir($this->tmpPath, 0777, true);
		$this->gatheredFiles = array();
		$this->gatherFiles($this->sources);
		$this->compressIfSpecified();
		$this->moveToDestination();
		$this->cleanup();
	}

	public function gatherFiles($sources) {
		foreach ($sources as $sourceName => $source) {
			if ($this->output->isVerbose()) {
				$this->output->writeln('Gathering Files from ' . $sourceName);
			}
			$class = '\Famelo\Beard\Backup\Sources\\' .  ucfirst($source['type']) . 'Source';
			$source = new $class($source);

			$sourcePath = path($this->tmpPath, $sourceName);
			if (!is_dir($sourcePath)) {
				mkdir($sourcePath);
			}

			$files = $source->gatherFiles($sourcePath);
			foreach ($files as $file) {
				$uploadFiles[] = $tmpFile;
			}

			if (count($files) == 0) {
				rmdir($sourcePath);
			}

			if (method_exists($source, 'getSources')) {
				$this->gatherFiles($source->getSources());
			}
		}
	}

	public function compressIfSpecified() {
		if ($this->compression !== NULL) {
			if ($this->output->isVerbose()) {
				$this->output->writeln('Compressing Files');
			}
			$zippy = Zippy::load();
			$archivePath = $this->tmpPath . '.' . $this->compression;
			$archive = $zippy->create($archivePath, array(
				basename($this->tmpPath) => $this->tmpPath . '/'), TRUE
			);
			$this->gatheredFiles = array($archivePath);
		}
	}

	public function moveToDestination() {
		foreach ($this->destinations as $name => $destination) {
			if ($this->output->isVerbose()) {
				$this->output->writeln('Moving Files to: ' . $name);
			}
			$adapter = new $destination['adapter']($destination);
			$filemanager = new Filesystem($adapter);
			if (!$filemanager->has($this->name)) {
				$filemanager->createDir($this->name);
			}
			foreach ($this->gatheredFiles as $uploadFile) {
				$filemanager->write(path($this->name, basename($uploadFile)), file_get_contents($uploadFile));
			}
			$backups = $filemanager->listPaths($this->name);
			foreach ($backups as $backup) {
				$metadata = $filemanager->getMetadata($backup);
				if ($metadata['timestamp'] > strtotime('-' . $this->days . ' days')) {
					continue;
				}
				if ($metadata['type'] == 'file') {
					$filemanager->delete($backup);
				} else {
					$filemanager->deleteDir($backup);
				}
			}
		}
	}

	public function cleanup() {
		$path = dirname($this->tmpPath);

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($files as $fileinfo) {
			$todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
			$todo($fileinfo->getRealPath());
		}

		rmdir($path);
	}
}

?>