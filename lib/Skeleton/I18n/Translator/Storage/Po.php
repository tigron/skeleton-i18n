<?php

namespace Skeleton\I18n\Translator\Storage;

class Po extends \Skeleton\I18n\Translator\Storage {

	/**
	 * strings
	 *
	 * @access private
	 * @var array $strings
	 */
	private $strings = null;

	/**
	 * Set storage path
	 *
	 * @access public
	 * @return string $storage_path
	 */
	private function get_storage_path() {
		$configuration = $this->get_configuration();
		if (!isset($configuration['storage_path'])) {
			throw new \Exception('Storage path not defined for Storage\\Po');
		}
		return $configuration['storage_path'];
	}

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
		$this->load_po();

		if (isset($this->strings[$string]) and $this->strings[$string] == $translated) {
			return;
		}

		if (isset($this->strings[$string]) and $translated === null) {
			return;
		}

		$this->strings[$string] = $translated;
		$this->write_po();
	}

	/**
	 * Add multiple translations
	 * Key = string
	 * Value = translated string
	 *
	 * @access public
	 * @param array $translations
	 */
	public function add_translations($translations) {
		if (!isset($this->language)) {
			throw new \Exception('Cannot add translation: Language not set');
		}
		$this->load_po();

		foreach ($translations as $string => $translation) {
			if (isset($this->strings[$string]) and $this->strings[$string] == $translation) {
				continue;
			}

			if (isset($this->strings[$string]) and $translation === null) {
				continue;
			}
			$this->strings[$string] = $translation;
		}
		$this->write_po();
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
		$this->load_po();
		return $this->strings[$string];
	}

	/**
	 * Load a po file
	 *
	 * @access private
	 */
	private function load_po() {
		$translated = \Skeleton\I18n\Util::load($this->get_storage_path() . '/' . $this->language->name_short . '/' . $this->name . '.po');
		$this->strings = $translated;
	}

	/**
	 * Write a po
	 *
	 * @access private
	 */
	private function write_po() {
		\Skeleton\I18n\Util::save(
			$this->get_storage_path() . '/' . $this->language->name_short . '/' . $this->name . '.po',
			$this->name,
			$this->language,
			$this->strings
		);
	}
}
