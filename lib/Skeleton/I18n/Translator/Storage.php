<?php

namespace Skeleton\I18n\Translator;

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
	 * Add a translation
	 *
	 * @access public
	 * @param string $string
	 * @param string $translated_string
	 */
	abstract public function add_translation($string, $translated_string);

	/**
	 * Get a translation
	 *
	 * @access public
	 * @param string $string
	 * @return string $translated_string
	 */
	abstract public function get_translation($string);

	/**
	 * Add multiple translations
	 * Key = string
	 * Value = translated string
	 *
	 * @access public
	 * @param array $translations
	 */
	public function add_translations($translations) {
		foreach ($translation as $key => $translation) {
			$this->add_translation($key, $translation);
		}
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
