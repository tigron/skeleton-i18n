<?php
/**
 * Translation class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\I18n;

class Translation {

	public $translator_storage = null;

	/**
	 * Translate
	 *
	 * @access public
	 * @param string $string
	 * @return string $translated
	 */
	public function translate($string) {
		$base_language = \Skeleton\I18n\Config::$base_language;
		if ($this->translator_storage->get_language()->name_short == $base_language) {
			return $string;
		}

		try {
			return $this->translator_storage->get_translation($string);
		} catch (\Exception $e) {
			if (\Skeleton\I18n\Config::$debug) {
				$string = '[NT] ' . $string;
			}
			return $string;
		}
	}

}
