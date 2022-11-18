<?php

namespace Skeleton\I18n\Translator\Extractor;

class Twig implements \Skeleton\I18n\Translator\Extractor, \Twig\NodeVisitor\NodeVisitorInterface {

	/**
	 * The twig template path
	 *
	 * @access private
	 * @var string $template_path
	 */
	private $template_path = null;

	/**
	 * Twig environment
	 *
	 * @access private
	 * @var \Twig\Environment $twig_environment
	 */
	private $twig_environemt = null;

	/**
	 * Set the template path
	 *
	 * @access public
	 * @param string $template_path
	 */
	public function set_template_path($template_path) {
		$this->template_path = $template_path;
	}

	/**
	 * Get strings
	 *
	 * @access public
	 * @return array $strings
	 */
	public function get_strings() {
		$templates = [];
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->template_path), \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
		    if ($file->isFile() === false) {
		        continue;
		    }

			if ($file->getExtension() != 'twig') {
				continue;
			}

		    $resource = substr($file, strlen($this->template_path));
		    $templates[] = $resource;
		}

		$strings = [];
		foreach ($templates as $template) {
			$strings = array_merge($strings, $this->extract($template));
		}
		return $strings;
	}

	/**
	 * get the twig environment
	 *
	 * @access protected
	 * @return \Twig\Environment $twig_environment
	 */
	protected function get_twig_environment() {
		if (!isset($this->twig_environment)) {
			$loader = new \Twig\Loader\FilesystemLoader($this->template_path);

			// force auto-reload to always have the latest version of the template
			$twig = new \Twig\Environment($loader, [
				'cache' => \Skeleton\Template\Twig\Config::$cache_path,
				'auto_reload' => true
			]);

			$twig->addExtension(new \Twig\Extension\StringLoaderExtension());
			$twig->addExtension(new \Skeleton\Template\Twig\Extension\Common());
			$twig->addExtension(new \Skeleton\I18n\Template\Twig\Extension\Tigron());
			$twig->addExtension(new \Twig\Extra\Markdown\MarkdownExtension());
			$twig->addExtension(new \Twig\Extra\String\StringExtension());
			$twig->addExtension(new \Twig\Extra\Cache\CacheExtension());

			$extensions = \Skeleton\Template\Twig\Config::get_extensions();
			foreach ($extensions as $extension) {
				$twig->addExtension(new $extension());
			}
			$twig->addNodeVisitor($this);
			$this->twig_environment = $twig;
		}
		return $this->twig_environment;
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
			$node = $this->get_twig_environment()->parse(
				$this->get_twig_environment()->tokenize(
					$this->get_twig_environment()->getLoader()->getSourceContext($resource),
					$resource
				)
			);
		} catch (\Twig\Error\SyntaxError $e) {
			echo 'Twig has thrown syntax error ' . $e->getMessage() . ': ' . $e->getSourceContext()->getPath() . ':' . $e->getTemplateLine();
			exit;
		} catch (\Exception $e) {
			var_dump($resource);
			echo 'Twig has thrown syntax error ' . $e->getMessage() . "\n";
		}

		return array_unique($this->extracted);
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
