<?php
/**
 * Twig Extractor
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\I18n\Extractor;

class Twig implements \Twig_NodeVisitorInterface {

	protected $extracted;

	public function __construct(\Twig_Environment $env) {
		$this->env = $env;
		$this->env->addNodeVisitor($this);
	}

	/**
	 * Defined by ExtractorInterface
	 *
	 * @param $resource Template name
	 */
	public function extract($resource) {
		$this->extracted = [];

		try {
			// Parse template
			$node = $this->env->parse(
				$this->env->tokenize(
					$this->env->getLoader()->getSource($resource),
					$resource
				)
			);
		} catch (\Twig_Error_Syntax $e) {
			echo 'Twig has thrown syntax error ' . $e->getMessage() . ' [' . $e->getTemplateLine() . ']';
			exit;
		}

		return $this->extracted;
	}

	/**
	 * Defined by Twig_NodeVisitorInterface
	 *
	 * Extracts messages from calls to the translate function.
	 */
	public function enterNode(\Twig_NodeInterface $node, \Twig_Environment $env) {
		if ($node instanceof \Skeleton\I18n\Template\Twig\Extension\Node\Trans\Tigron) {
			if ($node->getNodeTag() == 'trans') {
				try {
					$this->extracted[] = $node->getNode('body')->getAttribute('value');
				} catch (\Exception $e) {
					$this->extracted[] = $node->getNode('body')->getAttribute('data');
				}
			}
		} elseif ($node instanceof \Twig_Node_Print) {
			$n = $node->getNode('expr');
			while ($n instanceof \Twig_Node_Expression_Filter) {
				$filter = null;
				if ($n->hasNode('filter')) {
					$filter = $n->getNode('filter')->getAttribute('value');
				}
				$n = $n->getNode('node');
				if ($n instanceof \Twig_Node_Expression_Constant AND $filter == 'trans') {
					$this->extracted[] = $n->getAttribute('value');
				}
			}
		} elseif ($node instanceof \Twig_Node_Set) {
			$data = $node->getIterator();
			foreach ($data as $row) {
				$sub_nodes = $row->getIterator();
				foreach ($sub_nodes as $sub_node) {
					if ($sub_node instanceof \Twig_Node_Expression_Filter) {
						if ($sub_node->hasNode('filter') AND $sub_node->getNode('filter')->getAttribute('value') == 'trans') {
							$this->extracted[] = $sub_node->getNode('node')->getAttribute('value');
						}
					}
				}
			}
		} elseif ($node instanceof \Twig_Node_Expression_Array) {
			$data = $node->getIterator();
			foreach ($data as $row) {
				if ($row instanceof \Twig_Node_Expression_Filter) {
					if ($row->hasNode('filter') AND $row->getNode('filter')->getAttribute('value') == 'trans') {
						$this->extracted[] = $row->getNode('node')->getAttribute('value');
					}
				}
			}
		}

		return $node;
	}

	/**
	 * Defined by Twig_NodeVisitorInterface
	 */
	public function leaveNode(\Twig_NodeInterface $node, \Twig_Environment $env) {
		return $node;
	}

	/**
	 * Defined by Twig_NodeVisitorInterface
	 */
	public function getPriority() {
		return 0;
	}

}
