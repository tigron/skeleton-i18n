<?php
/**
 * Twig translation extension
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\I18n\Template\Twig\Extension;

class Tigron extends \Twig\Extension\AbstractExtension {
	/**
	 * Returns the token parser instances to add to the existing list.
	 *
	 * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
	 */
	public function getTokenParsers() {
		return [
			new TokenParser()
		];
	}

	/**
	 * Returns a list of filters to add to the existing list.
	 *
	 * @return array An array of filters
	 */
	public function getFilters() {
		$translation_filter = new \Twig\TwigFilter('trans', function (\Twig\Environment $env, $string) {
			$globals = $env->getGlobals();
			$translation = $globals['env']['translation'];
			return $translation->translate($string);
		}, ['needs_environment' => true]);
		return [
			$translation_filter
		];
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName() {
		return 'i18n';
	}
}
