<?php
namespace Famelo\Beard\PHPParser\Printer;

use PhpParser\Builder\Function_;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\PrettyPrinterAbstract;
use PhpParser\PrettyPrinter\Standard;

class TYPO3 extends Standard {

    /**
     * Pretty prints an array of nodes (statements) and indents them optionally.
     *
     * @param PHPParser_Node[] $nodes  Array of nodes
     * @param bool             $indent Whether to indent the printed nodes
     *
     * @return string Pretty printed statements
     */
    protected function pStmts(array $nodes, $indent = true) {
        $pNodes = array();
        foreach ($nodes as $node) {
            $pNodes[] = $this->pComments($node->getAttribute('comments', array()), $node)
                      . $this->p($node)
                      . ($node instanceof Expr ? ';' : '');
        }

        if ($indent) {
            return '    ' . preg_replace(
                '~\n(?!$|' . $this->noIndentToken . ')~',
                "\n" . '    ',
                implode("\n", $pNodes)
            );
        } else {
            return implode("\n", $pNodes);
        }
    }

    protected function pComments(array $comments, $node = null) {
        $result = '';

        foreach ($comments as $comment) {
            $result .= $comment->getReformattedText() . "\n";
        }

        if(empty($result)){
            if($node->getType() == 'Stmt_ClassMethod'){
                $result = "/**
* TODO: Document this Method! ( ".$node->name." )
*/\n";
            }

            if($node->getType() == 'Stmt_Property'){
                $result = "/**
* TODO: Document this Property!
*/\n";
            }
        }

        return $result;
    }



    protected $openArrays = 0;

    public function pExpr_Array(Array_ $node) {
        $this->openArrays++;
        $result = "array(\n" . $this->pCommaSeparatedWithNewLine($node->items) . "\n" . str_repeat("\t", $this->openArrays - 1) . ')';
        $this->openArrays--;
        return $result;
    }

    public function pExpr_ArrayItem(ArrayItem $node) {
        return str_repeat("\t", $this->openArrays) . (null !== $node->key ? $this->p($node->key) . ' => ' : '')
             . ($node->byRef ? '&' : '') . $this->p($node->value);
    }

    /**
     * Pretty prints an array of nodes and implodes the printed values with commas.
     *
     * @param PHPParser_Node[] $nodes Array of Nodes to be printed
     *
     * @return string Comma separated pretty printed nodes
     */
    protected function pCommaSeparatedWithNewLine(array $nodes) {
        return $this->pImplode($nodes, ",\n");
    }



//     // Declarations

    public function pStmt_Namespace(Namespace_ $node) {
        return "\n" . 'namespace' . (null !== $node->name ? ' ' . $this->p($node->name) : ''). ';' .
               "\n\n" . $this->pStmts($node->stmts, false) . "\n";
    }

    public function pStmt_Use(Use_ $node) {
        return "\nuse " . $this->pCommaSeparated($node->uses) . ";\n";
    }

    public function pStmt_UseUse(UseUse $node) {
        return $this->p($node->name)
             . ($node->name->getLast() !== $node->alias ? ' as ' . $node->alias : '');
    }

    public function pStmt_Interface(Interface_ $node) {
        return 'interface ' . $node->name
             . (!empty($node->extends) ? ' extends ' . $this->pCommaSeparated($node->extends) : '')
             . " " . '{' . "\n" . $this->pStmts($node->stmts) . "\n" . '}';
    }

    public function pStmt_Class(Class_ $node) {

        // Sort Alphabetically and put Properties above the methods
        $stmts = array();
        $methods = array();
        foreach ($node->stmts as $stmt) {
            if($stmt instanceof ClassMethod){
                $methods[$stmt->name] = $stmt;
            } else {
                $stmts[] = $stmt;
            }
        }

        ksort($stmts);

        $gs = array();
        $special = array();
        $injectors = array();
        $other = array();
        foreach ($methods as $method) {
            if(preg_match("/^(is|has|get|set|toggle|add|remove|update)(.+)/", $method->name, $match) > 0) {
                $gs[$match[2].$match[1]] = $method;
            } else if(preg_match("/^__(.+)/", $method->name, $match) > 0) {
                $special[$match[1]] = $method;
            } else if(preg_match("/^inject(.+)/", $method->name, $match) > 0) {
                $injectors[$match[1]] = $method;
            } else {
                $other[$method->name] = $method;
            }
        }

        ksort($gs);
        ksort($injectors);
        ksort($special);
        ksort($other);

        foreach ($injectors as $method)
            $stmts[] = $method;

        foreach ($special as $method)
            $stmts[] = $method;

        foreach ($gs as $method)
            $stmts[] = $method;

        foreach ($other as $method)
            $stmts[] = $method;

        $node->stmts = $stmts;

        return $this->pModifiers($node->type)
             . 'class ' . $node->name
             . (null !== $node->extends ? ' extends ' . $this->p($node->extends) : '')
             . (!empty($node->implements) ? ' implements ' . $this->pCommaSeparated($node->implements) : '')
             . ' {' . "\n\n" . $this->pStmts($node->stmts) . "\n" . '}';
    }

    public function pStmt_Trait(Trait_ $node) {
        return 'trait ' . $node->name
             . " " . '{' . "\n" . $this->pStmts($node->stmts) . "\n" . '}';
    }

    public function pStmt_Property(Property $node) {
        return $this->pModifiers($node->type) . $this->pCommaSeparated($node->props) . ";\n";
    }

    public function pStmt_ClassMethod(ClassMethod $node) {
        return $this->pModifiers($node->type)
             . 'function ' . ($node->byRef ? '&' : '') . $node->name
             . '(' . $this->pCommaSeparated($node->params) . ')'
             . (null !== $node->stmts
                ? " " . '{' . "\n" . $this->pStmts($node->stmts) . "\n" . "}\n"
                : ';');
    }

    public function pStmt_Function(Function_ $node) {
        return 'function ' . ($node->byRef ? '&' : '') . $node->name
             . '(' . $this->pCommaSeparated($node->params) . ')'
             . " " . '{' . "\n" . $this->pStmts($node->stmts) . "\n" . '}\n';
    }
}