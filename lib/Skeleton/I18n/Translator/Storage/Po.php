<?php

namespace Skeleton\I18n\Translator\Storage;

class Po extends \Skeleton\I18n\Translator\Storage {

	/**
	 * storage_path
	 *
	 * @access privavate
	 */
	private $storage_path = null;

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
	 * @param string $storage_path
	 */
	public function set_storage_path($storage_path) {
		$this->storage_path = $storage_path;
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

	private function load_po() {
		$translated = \Skeleton\I18n\Util::load($this->storage_path . '/' . $this->language->name_short . '/' . $this->name . '.po');	
		$this->strings = $translated;
	}
	private function write_po() {
		\Skeleton\I18n\Util::save(
			$this->storage_path . '/' . $this->language->name_short . '/' . $this->name . '.po',
			$this->name,
			$this->language,
			$this->strings
		);	
	}
		

}
