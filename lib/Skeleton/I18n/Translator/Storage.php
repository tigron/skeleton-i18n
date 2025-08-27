<?php

namespace Skeleton\I18n\Translator;

use Skeleton\Core\Skeleton;

abstract class Storage {

	/**
	 * storage_path
	 *
	 * @access private
	 */
	protected $name = null;

	/**
	 * Language
	 *
	 * @access private
	 * @var \Skeleton\I18n\LanguageInterface $language
	 */
	protected $language = null;

	/**
	 * Configuration
	 *
	 * @access protected
	 * @var array $configuration
	 */
	protected $configuration = [];

	/**
	 * Default configuration
	 *
	 * @access protected
	 * @var array $default_configuration
	 */
	protected static $default_configuration = [];

	/**
	 * strings
	 *
	 * @access private
	 * @var array $strings
	 */
	protected $strings = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {	}

	/**
	 * Set the name of the storage
	 *
	 * @access public
	 * @param string $name
	 */
	public function set_name($name) {
		$this->name = $name;
	}

	/**
	 * Set the language
	 *
	 * @access public
	 * @param \Skeleton\I18n\LanguageInterface $language
	 */
	public function set_language(\Skeleton\I18n\LanguageInterface $language) {
		$this->language = $language;
	}

	/**
	 * Set the language
	 *
	 * @access public
	 * @return \Skeleton\I18n\LanguageInterface $language
	 */
	public function get_language() {
		return $this->language;
	}

	/**
	 * Set configuration
	 *
	 * @access public
	 * @param array $configuration
	 */
	public function set_configuration($configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Get configuration
	 *
	 * @access public
	 * @return array $configuration
	 */
	public function get_configuration() {
		return array_merge(self::$default_configuration, $this->configuration);
	}

	/**
	 * Open the storage
	 *
	 * @access public
	 * @return void
	 */
	public function open(): void {
		if (!isset($this->language)) {
			throw new \Exception('Cannot open storage ' . get_class($this) . ': no language specified');
		}

		$translations = $this->load_cache_translations();
		if ($translations !== null) {
			// set the cached translations
			$this->strings[$this->language->name_short] = $translations;
			return;
		}

		$translations = $this->load_translations();
		if ($translations !== null) {
			$this->strings[$this->language->name_short] = $translations;
			$this->write_cache_translations();
		}
	}

	/**
	 * Load cached translations
	 *
	 * @access public
	 */
	public function load_cache_translations() {
		$cache_path = \Skeleton\I18n\Config::$cache_path;
		$cache_filename = $cache_path . '/' . $this->language->name_short . '/' . $this->name . '.php';

		if (!file_exists($cache_filename)) {
			// Not possible to load cache
			return null;
		}

		$last_modified = $this->get_last_modified();
		if ($last_modified === null) {
			// Cache disabled
			return null;
		}
		$cache_last_modified = new \DateTime();
		$cache_last_modified->setTimestamp(filemtime($cache_filename));

		if ($cache_last_modified >= $last_modified) {
			require $cache_filename;
			$translations = [];
			foreach ($strings as $key => $translation) {
				$translation_entry = new \Skeleton\I18n\Translation\Entry($translation['source']);
				$fuzzy = false;
				if (isset($translation['fuzzy'])) {
					$fuzzy = $translation['fuzzy'];
				}
				$translation_entry->set($translation['destination'], $fuzzy);
				$translations[$key] =  $translation_entry;
			}
			return $translations;
		}
		return null;
	}

	/**
	 * Write cached translations
	 *
	 * @access public
	 */
	public function write_cache_translations() {
		$cache_path = \Skeleton\I18n\Config::$cache_path;

		if (!file_exists($cache_path . '/' . $this->language->name_short)) {
			mkdir($cache_path . '/' . $this->language->name_short, 0755, true);
		}
		$cache_filename = $cache_path . '/' . $this->language->name_short . '/' . $this->name . '.php';

		$translation_array = $this->strings[$this->language->name_short];
		foreach ($this->strings[$this->language->name_short] as $key =>  $translation_entry) {
			$row['source'] = $translation_entry->source;
			$row['destination'] = $translation_entry->get_translation();
			$row['fuzzy'] = $translation_entry->is_fuzzy();
			$translation_array[$key] = $row;
		}

		file_put_contents($cache_filename, '<?php $strings = ' . var_export($translation_array, true) . ';');
		$last_modified = $this->get_last_modified();
		if ($last_modified === null) {
			touch($cache_filename);
		} else {
			touch($cache_filename, $this->get_last_modified()->getTimestamp());
		}
	}

	/**
	 * Write cached translations
	 *
	 * @access public
	 */
	public function invalidate_cache() {
		$cache_path = \Skeleton\I18n\Config::$cache_path;
		$cache_filename = $cache_path . '/' . $this->language->name_short . '/' . $this->name . '.php';
		if (file_exists($cache_filename)) {
			unlink($cache_filename);
		}
	}

	/**
	 * Add a translation
	 *
	 * @param mixed $string
	 * @param mixed $translated_string
	 * @param bool $fuzzy
	 * @return void
	 */
	public function add_translation($string, $translated_string, bool $fuzzy = false): void {
		$translation_entry = new \Skeleton\I18n\Translation\Entry($string);
		$translation_entry->set($translated_string, $fuzzy);
		$this->strings[$this->language->name_short][$string] = $translation_entry;
		$this->invalidate_cache();
	}


	/**
	 * Add a translation entry
	 *
	 * @param \Skeleton\I18n\Translation\Entry $entry
	 * @return void
	 */
	public function add_translation_entry(\Skeleton\I18n\Translation\Entry $entry): void {
		$this->strings[$this->language->name_short][$entry->source] = $entry;
		$this->invalidate_cache();
	}

	/**
	 * Update a translation entry
	 *
	 * @param \Skeleton\I18n\Translation\Entry $entry
	 * @return void
	 */
	public function update_translation_entry(\Skeleton\I18n\Translation\Entry $entry): void {
		$this->strings[$this->language->name_short][$entry->source] = $entry;
		$this->invalidate_cache();
	}

	/**
	 * Delete a translation
	 *
	 * @access public
	 * @param string $string
	 * @param string $translated_string
	 */
	public function delete_translation($string) {
		unset($this->strings[$this->language->name_short][$string]);
		$this->invalidate_cache();
	}

	/**
	 * Get a translation entry
	 *
	 * @access public
	 * @param string $string
	 * @return  \Skeleton\I18n\Translation\Entry $transaltion_entry
	 */
	public function get_translation_entry($string): \Skeleton\I18n\Translation\Entry {
		$translations = $this->get_translations();
		if (isset($translations[$string]) === false || $translations[$string]->is_translated() === false) {
			throw new \Exception('Translation entry not found for "' . $string . '"');
		}

		return $translations[$string];
	}


	/**
	 * Get a translation
	 *
	 * @access public
	 * @param string $string
	 * @return string $translated_string
	 */
	public function get_translation($string): string {
		$translation_entry = $this->get_translation_entry($string);
		return $translation_entry->get_translation();
	}


	/**
	 * Get all translations
	 *
	 * @access public
	 * @return array $translation
	 */
	public function get_translations(): array {
		if (!isset($this->strings[$this->language->name_short])) {
			throw new \Exception('Storage not opened');
		}

		return $this->strings[$this->language->name_short];
	}

	/**
	 * Close the storage
	 *
	 * @access public
	 * @return void
	 */
	public function close(): void {
		if (!isset($this->language)) {
			throw new \Exception('Cannot open storage ' . get_class($this) . ': no language specified');
		}

		if (!isset($this->strings[$this->language->name_short])) {
			throw new \Exception('Storage not opened');
		}

		$this->save_translations();
	}

	/**
	 * Empty
	 *
	 * @access public
	 */
	public function empty(): void {
		$this->strings[$this->language->name_short] = [];
		$this->save_translations();
		$this->invalidate_cache();
	}

	/**
	 * Load translations
	 *
	 * @access public
	 * @return array $translations
	 */
	abstract public function load_translations(): ?array;

	/**
	 * Save translations
	 *
	 * @access public
	 * @return array $translations
	 */
	abstract public function save_translations(): void;

	/**
	 * Get last modified
	 *
	 * @access public
	 * @return \Datetime $last_modified
	 */
	abstract public function get_last_modified(): ?\Datetime;

	/**
	 * migrate
	 *
	 * @access public
	 * @param \Skeleton\I18n\Translator\Storage $storage
	 */
	public function migrate(\Skeleton\I18n\Translator\Storage $storage): void {
		$translations = $this->get_translations();
		$storage->open();
		foreach ($translations as $string => $translated) {
			$storage->add_translation($string, $translated);
		}
		$storage->close();
	}

	/**
	 * Set default configuration
	 *
	 * @access public
	 * @param array $default_configuration
	 */
	public static function set_default_configuration($default_configuration) {
		self::$default_configuration = $default_configuration;
	}

	/**
	 * Get default configuration
	 *
	 * @access public
	 * @return array $default_configuration
	 */
	public static function get_default_configuration() {
		return self::$default_configuration;
	}
}
