<?php
namespace Famelo\Beard\Tests\Functional\Scaffold\Typo3\Components;

use org\bovigo\vfs\vfsStream;
use Famelo\Beard\Scaffold\Builder\ComposerBuilder;

/**
 * Class ClassBuilderTest
 */
class MetadataComponentTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @return void
	 */
	public static function setUpBeforeClass() {
		vfsStream::setup('foo/');
	}

	/**
	 * @test
	 */
	public function createNew() {
		$filepath = vfsStream::url('foo/composer.json');

		$builder = new ComposerBuilder($filepath);
		$builder->setNamespace('Hello\World', 'src');
		$builder->save($filepath);

		$composerData = json_decode(file_get_contents($filepath), TRUE);
		$this->assertTrue(isset($composerData['autoload']['psr-4']['Hello\World']));
		$this->assertEquals('src\\', $composerData['autoload']['psr-4']['Hello\World']);
		$this->asse
	}

}
