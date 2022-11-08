<?php

namespace Skeleton\I18n\Translator;

abstract class Storage {

	/**
	 * storage_path
	 *
	 * @access privavate
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

}
