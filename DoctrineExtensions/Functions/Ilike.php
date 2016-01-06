<?php

namespace Librinfo\CoreBundle\DoctrineExtensions\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode,
    Doctrine\ORM\Query\Lexer;

class Ilike extends FunctionNode
{

    protected $field;
    protected $value;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->value = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
        dump($this->field, $this->value);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return $this->field->dispatch($sqlWalker) . ' ILIKE ' . $this->value->dispatch($sqlWalker);
    }

}
