<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Skeleton\I18n\Template\Twig\Extension\Node\Trans;

/**
 * Represents a trans node.
 *
 * @package    twig
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Tigron extends \Twig\Node\Node
{
    public function __construct($name, \Twig\Node\Expression\ConstantExpression $value, $line, $tag = null)
    {
        parent::__construct([ 'value' => $value ], [ 'name' => $name ], $line, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        list($msg, $vars) = $this->compileString($this->getNode('value'));

        /*if (null !== $this->getNode('plural')) {
            list($msg1, $vars1) = $this->compileString($this->getNode('plural'));

            $vars = array_merge($vars, $vars1);
        }*/

        $function = '$context[\'env\'][\'translation\']->translate';

        if ($vars) {
            $compiler
                ->write('echo strtr('.$function.'(')
                ->subcompile($msg)
            ;

            /*if (null !== $this->getNode('plural')) {
                $compiler
                    ->raw(', ')
                    ->subcompile($msg1)
                    ->raw(', abs(')
                    ->subcompile($this->getNode('count'))
                    ->raw(')')
                ;
            }*/

            $compiler->raw(')');

            foreach ($vars as $var) {
                if ('count' === $var->getAttribute('name')) {
                    $compiler
                        ->string('%count%')
                        ->raw(' => abs(')
                        ->subcompile($this->getNode('count'))
                        ->raw('), ')
                    ;
                } else {
                    $compiler
                        ->string('%'.$var->getAttribute('name').'%')
                        ->raw(' => ')
                        ->subcompile($var)
                        ->raw(', ')
                    ;
                }
            }

            $compiler->raw("));\n");
        } else {
            $compiler
                ->write('echo '.$function.'(')
                ->subcompile($msg)
            ;

            /*if (null !== $this->getNode('plural')) {
                $compiler
                    ->raw(', ')
                    ->subcompile($msg1)
                    ->raw(', abs(')
                    ->subcompile($this->getNode('count'))
                    ->raw(')')
                ;
            }*/

            $compiler->raw(");\n");
        }
    }

    protected function compileString(\Twig\Node\Expression\ConstantExpression $value)
    {
        if (
            $value instanceof \Twig\Node\Expression\NameExpression
            || $value instanceof \Twig\Node\Expression\ConstantExpression
            || $value instanceof \Twig\Node\Expression\TempNameExpression
        ) {
            return [$value, []];
        }

        $vars = [];
        if (count($value)) {
            $msg = '';

            foreach ($value as $node) {
                if (get_class($node) === 'Twig_Node' && $node->getNode(0) instanceof \Twig_Node_SetTemp) {
                    $node = $node->getNode(1);
                }

                if ($node instanceof \Twig_Node_Print) {
                    $n = $node->getNode('expr');
                    while ($n instanceof \Twig_Node_Expression_Filter) {
                        $n = $n->getNode('node');
                    }
                    $msg .= sprintf('%%%s%%', $n->getAttribute('name'));
                    $vars[] = new \Twig_Node_Expression_Name($n->getAttribute('name'), $n->getLine());
                } else {
                    $msg .= $node->getAttribute('data');
                }
            }
        } else {
            $msg = $value->getAttribute('data');
        }

        return [new \Twig_Node([new \Twig_Node_Expression_Constant(trim($msg), $value->getLine())]), $vars];
    }
}
