<?php

namespace Skeleton\I18n\Translator\Storage;

class Po extends \Skeleton\I18n\Translator\Storage {

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
	 * Load translations
	 *
	 * @access public
	 * @return array $translations
	 */

	 public function load_translations(): ?array {
		if (isset($this->strings[$this->language->name_short])) {
			return null;
		}

		return \Skeleton\I18n\Util::load($this->get_storage_path() . '/' . $this->language->name_short . '/' . $this->name . '.po');
	}

	/**
	 * Write a po
	 *
	 * @access private
	 */
	public function save_translations(): void {
		$strings = $this->get_translations();
		$fuzzies = $this->get_fuzzies();

		\Skeleton\I18n\Util::save(
			$this->get_storage_path() . '/' . $this->language->name_short . '/' . $this->name . '.po',
			$this->name,
			$this->language,
			$strings,
			$fuzzies
		);
	}

	/**
	 * Get last modified
	 *
	 * @access public
	 * @return \Datetime $last_modified
	 */
	public function get_last_modified(): ?\Datetime {
		return null;
	}
}
