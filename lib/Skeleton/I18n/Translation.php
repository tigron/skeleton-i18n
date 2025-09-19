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

	/**
	 * The translator_storage
	 *
	 * @access public
	 * @var \Skeleton\I18n\Translator\Storage $translator_storage
	 */
	public $translator_storage = null;

	/**
	 * Language
	 *
	 * @access private
	 * @var \Skeleton\I18n\LanguageInterface $language
	 */
	public $language = null;

	/**
	 * Cache
	 *
	 * @access private
	 */
	private static $cache = [];

	/**
	 * Translate
	 *
	 * @access public
	 * @param string $string
	 * @return string $translated
	 */
	public function translate($string) {
		if ($this->translator_storage->get_language()->is_base()) {
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

	/**
	 * Get
	 * This method is here for backwards compatibility
	 *
	 * @access public
	 * @param \Skeleton\I18n\LanguageInterface $language
	 * @param string $name
	 * @return Translation $translation
	 */
	public static function get(\Skeleton\I18n\LanguageInterface $language, $name) {
		$translator = Translator::get_by_name($name);
		$translation = $translator->get_translation($language);
		return $translation;
	}

	/**
	 * Get by translator_language
	 *
	 * @access public
	 * @param \Skeleton\I18n\Translator $translator
	 * @param \Skeleton\I18n\LanguageInterface $language
	 * @return Translation $translation
	 */
	public static function get_by_translator_language(\Skeleton\I18n\Translator $translator, \Skeleton\I18n\LanguageInterface $language): self {
		if (!isset(self::$cache[$translator->get_name()][$language->name_short])) {
			$translation = new self();
			$translator_storage = $translator->get_translator_storage();
			$translator_storage->set_language($language);
			$translator_storage->set_name($translator->get_name());
			$translator_storage->open();
			$translation->translator_storage = $translator_storage;
			$translation->language = $language;

			self::$cache[$translator->get_name()][$language->name_short] = $translation;
		}
		return self::$cache[$translator->get_name()][$language->name_short];
	}
}
