<?php
namespace Famelo\Beard\Scaffold\Builder\Php;

use Famelo\Beard\Scaffold\Builder\Php\Printer\TYPO3Printer;
use PhpParser\BuilderFactory;
use PhpParser\ParserFactory;

/**
 */
abstract class AbstractClassBuilder {

	/**
	 * @var string
	 */
	protected $filepath;

	/**
	 * @var object
	 */
	protected $parser;

	/**
	 * @var array
	 */
	protected $statements = array();

	/**
	 * initialize a new php class
	 *
	 * @param string $filepath
	 */
	public function __construct($filepath) {
		$this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
		$this->factory = new BuilderFactory;
		if (file_exists($filepath)) {
			$this->statements = $this->parser->parse(file_get_contents($filepath));
		}
		$this->filepath = $filepath;
	}

	/**
	 * save this class to a file
	 *
	 * @param string $targetFileName
	 * @return void
	 */
	public function save($targetFileName) {
		$prettyPrinter = new TYPO3Printer;

		if (!file_exists(dirname($targetFileName))) {
			mkdir(dirname($targetFileName), 0775, TRUE);
		}

		try {
			$code = '<?php ' .  chr(10) . $prettyPrinter->prettyPrint($this->statements);
			file_put_contents($targetFileName, $code);
		} catch (Error $e) {
			echo 'Parse Error: ', $e->getMessage();
		}
	}

	/**
	 * parse a piece of code
	 *
	 * @param string $code
	 * @param string $type
	 * @return void
	 */
	public function parse($code, $type = 'file') {
		switch ($type) {
			case 'property':
			case 'method':
					$code = '<?php class foo {' . $code . '}';
				break;
		}

		$statements = $this->parser->parse($code);

		switch ($type) {
			case 'property':
			case 'method':
					$statements = $statements[0]->stmts;
				break;
		}
		return $statements;
	}

	/**
	 * replace a string in a statement
	 *
	 * @param object $statement
	 * @param string $replacements
	 * @param string $type
	 * @return object
	 */
	public function replaceStrings($statement, $replacements, $type="file") {
		$prettyPrinter = new TYPO3Printer;
		$code = $prettyPrinter->prettyPrint(array($statement));
		$code = str_replace(array_keys($replacements), $replacements, $code);
		return current($this->parse($code, $type));
	}
}

?>
