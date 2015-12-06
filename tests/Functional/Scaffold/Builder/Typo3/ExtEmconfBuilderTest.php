<?php
namespace Famelo\Beard\Tests\Functional\Scaffold\Builder\Typo3;

use org\bovigo\vfs\vfsStream;
use Famelo\Beard\Scaffold\Builder\Typo3\ExtEmconfBuilder;

/**
 * Class ExtEmconfBuilderTest
 */
class ExtEmconfBuilderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @return void
	 */
	public static function setUpBeforeClass() {
		vfsStream::setup('foo/');
	}

	/**
	 * @test
	 */
	public function create() {
		$filepath = vfsStream::url('foo/ext_emconf.php');

		$builder = new ExtEmconfBuilder($filepath);
		$builder->addDependency('foo_bar', '1.0.0');
		$builder->save($filepath);

		$fileContent = file_get_contents($filepath);
		$this->assertContains("'foo_bar' => '1.0.0'", $fileContent);
	}

	/**
	 * @test
	 */
	public function update() {
		$filepath = vfsStream::url('foo/ext_emconf.php');
		file_put_contents($filepath, '<?php
		$EM_CONF[$_EXTKEY] = array (
			"constraints" => array (
				"depends" => array (
  					"foo_bar" => "1.0.0"
				)
			)
		);');

		$builder = new ExtEmconfBuilder($filepath);
		$builder->removeDependency('foo_bar');
		$builder->save($filepath);

		$fileContent = file_get_contents($filepath);
		$this->assertNotContains("'foo_bar' => '1.0.0'", $fileContent);
	}

}
