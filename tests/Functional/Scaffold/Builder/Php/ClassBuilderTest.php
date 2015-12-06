<?php
namespace Famelo\Beard\Tests\Functional\Scaffold\Builder\Php;

use org\bovigo\vfs\vfsStream;
use Famelo\Beard\Scaffold\Builder\Php\ClassBuilder;

/**
 * Class ClassBuilderTest
 */
class ClassBuilderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @return void
	 */
	public static function setUpBeforeClass() {
		vfsStream::setup('src/');
	}

	/**
	 * @test
	 */
	public function builderCreatesANewClass() {
		$filepath = vfsStream::url('src/Foo.php');

		$builder = new ClassBuilder($filepath);
		$builder->setNamespace('Hello\World');
		$builder->setClassName('Foo');
		$builder->addMethod('test');
		$builder->save($filepath);

		$generatedCode = file_get_contents($filepath);
		$this->assertContains('namespace Hello\World;', $generatedCode);
		$this->assertContains('class Foo {', $generatedCode);
		$this->assertContains('public function test() {', $generatedCode);
	}

}
