<?php

namespace Skeleton\I18n\Translator\Storage;

class Database extends \Skeleton\I18n\Translator\Storage {

	/**
	 * strings
	 *
	 * @access private
	 * @var array $strings
	 */
	private $strings = null;

	/**
	 * Add a translation
	 *
	 * @access public
	 * @param string $string
	 * @param string $translated_string
	 */
	public function add_translation($string, $translated) {
		if (!isset($this->language)) {
			throw new \Exception('Cannot add translation: Language not set');
		}
	}

	/**
	 * Get a translation
	 *
	 * @access public
	 * @param string $string
	 * @return string $translated_string
	 */
	public function get_translation($string) {
		if (!isset($this->language)) {
			throw new \Exception('Cannot get translation: Language not set');
		}
		// FIXME: not sure, but this shortcut will prevent reloading a new translation for an already loaded string
		if (isset($this->strings[$string])) {
			return $this->strings[$string];
		}
		try {
			$translation_source = \Skeleton\I18n\Translator\Storage\Database\Translation\Source::get_by_name_string($this->name, $string);
			$translation_target = \Skeleton\I18n\Translator\Storage\Database\Translation\Target::get_by_source_language($translation_source, $this->language);
			$this->strings[$string] = $translation_target->translation;
			return $this->strings[$string];
		} catch (\Exception $e) {
			return $string;
		}
	}
}