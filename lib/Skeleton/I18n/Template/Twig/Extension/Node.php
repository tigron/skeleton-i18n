<?php
/**
 * Twig translation (node)
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\I18n\Template\Twig\Extension;

class Node extends \Twig_Node {
	public function __construct(\Twig_NodeInterface $body, \Twig_NodeInterface $plural = null, \Twig_Node_Expression $count = null, $lineno, $tag = null) {
		parent::__construct(['count' => $count, 'body' => $body, 'plural' => $plural], [], $lineno, $tag);
	}

	/**
	 * Compiles the node to PHP.
	 *
	 * @param Twig_Compiler A Twig_Compiler instance
	 */
	public function compile(\Twig_Compiler $compiler) {
		$compiler->addDebugInfo($this);

		list($msg, $vars) = $this->compileString($this->getNode('body'));

		if (null !== $this->getNode('plural')) {
			list($msg1, $vars1) = $this->compileString($this->getNode('plural'));

			$vars = array_merge($vars, $vars1);
		}

		$function = null === $this->getNode('plural') ? 'Translation::translate' : 'Translation::translate_plural';

		if ($vars) {
			$compiler
				->write('echo strtr('.$function.'(')
				->subcompile($msg)
			;

			if (null !== $this->getNode('plural')) {
				$compiler
					->raw(', ')
					->subcompile($msg1)
					->raw(', abs(')
					->subcompile($this->getNode('count'))
					->raw(')')
				;
			}

			$compiler->raw(', $context[\'env\'][\'translation\']), array(');

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

			if (null !== $this->getNode('plural')) {
				$compiler
					->raw(', ')
					->subcompile($msg1)
					->raw(', abs(')
					->subcompile($this->getNode('count'))
					->raw(')')
				;
			}

			$compiler->raw(", " . '$context[\'env\'][\'translation\']' . ");\n");
		}
	}

	protected function compileString(\Twig_NodeInterface $body) {
		if ($body instanceof \Twig_Node_Expression_Name || $body instanceof \Twig_Node_Expression_Constant || $body instanceof \Twig_Node_Expression_TempName) {
			return [$body, []];
		}

		$vars = [];
		if (count($body)) {
			$msg = '';

			foreach ($body as $node) {
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
			$msg = $body->getAttribute('data');
		}

		return [new \Twig_Node([new \Twig_Node_Expression_Constant(trim($msg), $body->getLine())]), $vars];
	}
}
