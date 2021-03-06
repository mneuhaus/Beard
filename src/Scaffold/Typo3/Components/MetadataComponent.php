<?php
namespace Famelo\Beard\Scaffold\Typo3\Components;

use Famelo\Beard\Utility\Path;
use Famelo\Beard\Utility\StringUtility;
use Famelo\Beard\Scaffold\Core\Components\AbstractComponent;
use Famelo\Beard\Scaffold\Builder\ComposerBuilder;
use Symfony\Component\Finder\Finder;

/*
 * This file belongs to the package "Famelo Soup".
 * See LICENSE.txt that was shipped with this package.
 */

class MetadataComponent extends AbstractComponent {
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var array
	 */
	public $data = array(
		'title' => 'bar',
		'description' => NULL,
		'category' => NULL,
		'author' => NULL,
		'author_email' => NULL,
		'version' => NULL,
		'state' => 'alpha',
		'internal' => '',
		'uploadfolder' => '1',
		'createDirs' => '',
		'clearCacheOnLoad' => 0,
		'constraints' => array(
			'depends' => array(),
			'conflicts' => array(),
			'suggests' => array()
		)
	);

	/**
	 * @var string
	 */
	protected $composer = array(
		"autoload" => array(
			"psr-4" => array(
			)
		)
	);

	/**
	 * @var string
	 */
	protected $filepath;

	/**
	 * @var string
	 */
	protected $extensionKey;

	/**
	 * @var array
	 */
	static protected $paths = array(
		'Classes/Controller/'
	);

	public function __construct($filepath = NULL) {
		if ($filepath === NULL || !file_exists($filepath)) {
			$filepath = Path::joinPaths(BASE_DIRECTORY, '../Resources/CodeTemplates/Typo3/ext_emconf.php');
		} else {
			$this->extensionKey = basename(getcwd());
			$this->filepath = $filepath;
		}
		if (file_exists($filepath)) {
			$_EXTKEY = 'foo';
			$EM_CONF = array();
			require($filepath);
			foreach ($EM_CONF[$_EXTKEY] as $key => $value) {
				$this->data[strval($key)] = $value;
			}
		}

		$this->composer = new ComposerBuilder(Path::joinPaths(dirname($filepath), 'composer.json'));
	}

	public static function getComponents() {
		return array(new MetadataComponent('ext_emconf.php'));
	}

	public function getArguments() {
		return array($this->filepath);
	}

	public function getTitle() {
		return $this->data['title'];
	}

	public function getFilepath() {
		return $this->filepath;
	}

	public function save($fieldValues) {
		foreach ($fieldValues as $key => $value) {
			if (isset($this->data[$key])) {
				$this->data[$key] = $value;
			}
		}

		$output = sprintf('<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "%s"
 *
 * Auto generated by famelo/soup %s
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = %s;',
			basename(WORKING_DIRECTORY),
			date('Y-m-d'),
			var_export($this->data, TRUE)
		);
		file_put_contents('ext_emconf.php', $output);

		$namespace = '';
		if (!empty($fieldValues['company'])) {
			$namespace = $fieldValues['company'] . '\\';
		}
		$namespace.= StringUtility::underscoreToCamelcase($fieldValues['extension_key']) . '\\';
		$this->composer->setNamespace($namespace, 'Classes/');
		$this->composer->save();
	}

	public function getDescription() {
		return $this->data['description'];
	}

	public function getAuthor() {
		return $this->data['author'];
	}

	public function getAuthorEmail() {
		return $this->data['author_email'];
	}

	public function getExtensionKey() {
		return $this->extensionKey;
	}

	public function getCompany() {
		$namespace = $this->composer->getNamespace();
		if (stristr($namespace, '\\')) {
			$parts = explode('\\', $namespace);
			return array_shift($parts);
		}
	}

	public function getExtensionTypes() {
		return array(
			'fe' => 'Frontend',
			'plugin' => 'Frontend Plugins',
			'be' => 'Backend',
			'module' => 'Backend Modules',
			'services' => 'Services',
			'example' => 'Examples',
			'misc' => 'Miscellaneous',
			'templates' => 'Templates',
			'doc' => 'Documentation'
		);
	}

	public function getExtensionType() {
		return $this->data['category'];
	}

	public function getExtensionStates() {
		return array(
			'alpha' => 'Alpha (Very initial development)',
			'beta' => 'Beta (Under current development, should work partly)',
			'stable' => 'Stable (Stable and used in production)',
			'experimental' => 'Experimental (Nobody knows if this is going anywhere yet...)',
			'test' => 'Test (Test extension, demonstrates concepts etc.)'
		);
	}

	public function getExtensionState() {
		return $this->data['state'];
	}

	public function getVersion() {
		return $this->data['version'];
	}
}
