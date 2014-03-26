<?php

namespace Famelo\Beard\Command;

use Famelo\Beard\PHPParser\Printer\TYPO3;
use Doctrine\Common\Inflector\Inflector;
use PhpParser\BuilderFactory;
use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\Template;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Patch command.
 *
 */
class Refactor extends Command {

	/**
	 * The output handler.
	 *
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * @var string
	 */
	protected $baseDir;

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('refactor');
		$this->setDescription('Refactor PHP Files to comply with TYPO3 CGL');

		$this->addArgument('file', InputArgument::REQUIRED);

		$this->addOption('add-getter-setter', FALSE, InputOption::VALUE_NONE,
			'Adds missing getters and setters'
        );
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;
		$file = realpath($input->getArgument('file'));

		$code = file_get_contents($file);

		$this->parser  = new Parser(new Lexer);
		$this->factory = new BuilderFactory;
		$this->templateBuilder = $this->getTemplateBuilder($this->parser);
		$prettyPrinter = new TYPO3;

		try {
			$code = str_replace("\\n", "<<<newline>>>", $code);
		    // parse
		    $stmts = $this->parser->parse($code);

		    if ($this->input->getOption('add-getter-setter') === TRUE) {
		    	$this->addGetterSetter($stmts);
		    }

		    // pretty print
		    $code = '<?php' . $prettyPrinter->prettyPrint($stmts) . "\n?>";

		    $code = preg_replace("/;\n\n\nuse /", ";\nuse ", $code);
		    $code = preg_replace("/\*\/\n\/\*\*/", "*/\n\n/**", $code);
		    $code = preg_replace("/\{\n\n\n\}/s", "{}", $code);
			$code = preg_replace("/\s*$/", "", $code);
			$code = preg_replace("/array\\([\s\n]+/s", "array(", $code);

			$lines = explode("\n", $code);
		    foreach ($lines as $key => $line) {
		    	$lines[$key] = rtrim($line);
		    }
		    $code = implode("\n", $lines);

			$code = str_replace("<<<newline>>>", "\\n", $code);

			$this->output->write($code);
		} catch (Error $e) {
		    echo 'Parse Error: ', $e->getMessage();
		}

	}

	protected function addGetterSetter($stmts) {
		if ($stmts[0] instanceof \PhpParser\Node\Stmt\Namespace_) {
			$this->addGetterSetter($stmts[0]->stmts);
		}
		foreach ($stmts as $stmt) {
			if ($stmt instanceof \PhpParser\Node\Stmt\Class_) {
				$properties = array();
				$classMethods = array();
				foreach ($stmt->stmts as $classStmt) {
					if ($classStmt instanceof \PhpParser\Node\Stmt\Property) {
						$properties[] = $classStmt;
					}
					if ($classStmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
						$classMethods[$classStmt->name] = $classStmt;
					}
				}

				$methods = array(
					'get', 'set', 'add', 'remove'
				);

				foreach ($properties as $propertyStmt) {
					$propertyName = $propertyStmt->props[0]->name;
					$propertyType = $this->getPropertyType($propertyStmt);
					$propertySubType = $this->getPropertySubType($propertyStmt);

					foreach ($methods as $method) {
						$methodName = $method . ucfirst($propertyName);

						if (isset($classMethods[$methodName])) {
							continue;
						}

						if ($method == 'add' || $method == 'remove') {
							if (stristr($propertyType, '<') !== FALSE) {
								$method = $method . 'Collection';
							} else if(substr($propertyType, 0, 5) === 'array') {
								$method = $method . 'Array';
							} else {
								continue;
							}

							$singularPropertName = Inflector::singularize($propertyName);

							$methodNode = $this->getMethod($method, array(
								'name' 	=> $propertyName,
								'singular' => $singularPropertName,
								'type' 	=> $propertySubType
							));
							$stmt->stmts = array_merge($stmt->stmts, $methodNode);
						} else {
							$methodNode = $this->getMethod($method, array(
								'name' 	=> $propertyName,
								'type' 	=> $propertyType
							));
							$stmt->stmts = array_merge($stmt->stmts, $methodNode);
						}
					}
				}
			}
		}
	}

	public function getTemplateBuilder($parser) {
		$templateString = '<?php
class Get {
    /**
     * Gets __name__.
     *
     * @return __type__ $__name__
     */
    public function __Name__() {
        return $this->__name__;
    }
}
';
		return new Template($parser, $templateString);
	}

	public function getMethod($template, $replacements) {
		$templates = array(
			'get' => '<?php
class Get {
    /**
     * Gets __name__.
     *
     * @return __type__ $__name__
     */
    public function get__Name__() {
        return $this->__name__;
    }
}',
			'set' => '<?php
class Set {
    /**
     * Sets the __name__.
     *
     * @param __type__ $__name__
     */
    public function set__Name__($__name__) {
        $this->__name__ = $__name__;
    }
}',
			'addCollection' => '<?php
class AddCollection {
    /**
     * Add to the __name__.
     *
     * @param __type__ $__singular__
     */
    public function add__Singular__($__singular__) {
        $this->__name__->add($__singular__);
    }
}',
			'addArray' => '<?php
class AddArray {
    /**
     * Add to the __name__.
     *
     * @param __type__ $__singular__
     */
    public function add__Singular__($__singular__) {
        $this->__name__[] = $__singular__;
    }
}',
			'removeArray' => '<?php
class RemoveArray {
    /**
     * Remove from __name__.
     *
     * @param __type__ $__singular__
     */
    public function remove__Singular__($__singular__) {
    	unset($this->__name__[array_search($__singular__, $this->__name__)]);
    }
}',
			'removeCollection' => '<?php
class RemoveCollection {
    /**
     * Remove from __name__.
     *
     * @param __type__ $__singular__
     */
    public function remove__Singular__($__singular__) {
        $this->__name__->remove($__singular__);
    }
}'
		);

		$template = new Template($this->parser, $templates[$template]);

		$node = $template->getStmts($replacements);

		return $node[0]->stmts;

	}

	protected function getComment($node) {
        $result = '';

        foreach ($node->getAttribute('comments', array()) as $comment) {
            $result .= $comment->getReformattedText() . "\n";
        }

        return $result;
    }

	protected function getPropertyType($node) {
        $comment = $this->getComment($node);
        preg_match('/@var ([^ ]*)/', $comment, $match);
        if (count($match) > 1) {
        	return trim($match[1]);
        }
    }

	protected function getPropertySubType($node) {
        $comment = $this->getComment($node);
        preg_match('/@var ([^ <]*)<*([A-Za-z0-9\\\\]*)>*/', $comment, $match);
        if (count($match) > 2) {
        	if (trim($match[2]) !== '') {
        		return trim($match[2]);
        	}
        }
        return 'mixed';
    }
}

?>