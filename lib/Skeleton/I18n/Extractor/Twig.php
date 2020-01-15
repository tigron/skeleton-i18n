<?php
/**
 * Twig Extractor
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David V&&emaele <david@tigron.be>
 */

namespace Skeleton\I18n\Extractor;

class Twig implements \Twig\NodeVisitor\NodeVisitorInterface {

	protected $extracted;

	public function __construct(\Twig\Environment $env) {
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
					$this->env->getLoader()->getSourceContext($resource),
					$resource
				)
			);
		} catch (\Twig\Error\SyntaxError $e) {
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
	public function enterNode(\Twig\Node\Node $node, \Twig\Environment $env): \Twig\Node\Node {
		if ($node instanceof \Skeleton\I18n\Template\Twig\Extension\Node\Trans\Tigron) {
			if ($node->getNodeTag() == 'trans') {
				$extracted = null;

				try {
					$extracted = $node->getNode('value')->getAttribute('value');
				} catch (\Exception $e) {}

				try {
					$extracted = $node->getNode('value')->getAttribute('data');
				} catch (\Exception $e) {}

				if ($extracted === null) {
					throw new \Exception('Template syntax error in ' . $node->getNode('value')->getTemplateName() . ' on line ' . $node->getNode('value')->getTemplateLine());
				}

				$this->extracted[] = $extracted;
			}
		} elseif ($node instanceof \Twig\Node\PrintNode) {
			$n = $node->getNode('expr');
			while ($n instanceof \Twig\Node\Expression\FilterExpression) {
				$filter = null;
				if ($n->hasNode('filter')) {
					$filter = $n->getNode('filter')->getAttribute('value');
				}
				$n = $n->getNode('node');
				if ($n instanceof \Twig\Node\Expression\ConstantExpression && $filter == 'trans') {
					$this->extracted[] = $n->getAttribute('value');
				}
			}
		} elseif ($node instanceof \Twig\Node\SetNode) {
			$data = $node->getIterator();
			foreach ($data as $row) {
				$sub_nodes = $row->getIterator();
				foreach ($sub_nodes as $sub_node) {
					if ($sub_node instanceof \Twig\Node\Expression\FilterExpression) {
						if ($sub_node->hasNode('filter') && $sub_node->getNode('filter')->getAttribute('value') == 'trans') {
							$this->extracted[] = $sub_node->getNode('node')->getAttribute('value');
						}
					}
				}
			}
		} elseif ($node instanceof \Twig\Node\Expression\ArrayExpression) {
			$data = $node->getIterator();
			foreach ($data as $row) {
				if ($row instanceof \Twig\Node\Expression\FilterExpression) {
					if ($row->hasNode('filter') && $row->getNode('filter')->getAttribute('value') == 'trans') {
						$this->extracted[] = $row->getNode('node')->getAttribute('value');
					}
				}
			}
		} elseif ($node instanceof \Twig\Node\Expression\FilterExpression) {
			$data = $node->getIterator();
			foreach ($data as $row) {
				try {
					if ($row->hasNode('filter') && $row->getNode('filter')->getAttribute('value') == 'trans') {
						$this->extracted[] = $row->getNode('node')->getAttribute('value');
					}
				} catch (\Exception $e) {}
			}
		}

		return $node;
	}

	/**
	 * Defined by Twig_NodeVisitorInterface
	 */
	public function leaveNode(\Twig\Node\Node $node, \Twig\Environment $env): \Twig\Node\Node {
		return $node;
	}

	/**
	 * Defined by Twig_NodeVisitorInterface
	 */
	public function getPriority() {
		return 0;
	}
}
