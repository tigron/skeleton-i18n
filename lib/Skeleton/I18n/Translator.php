<?php
/**
 * Translator
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 */

namespace Skeleton\I18n;

class Translator {

	/**
	 * Name
	 *
	 * @acess public
	 * @var string $name
	 */
	private $name = null;

	/**
	 * Translator_Storage
	 *
	 * @acess private
	 * @var Translator_Storage $translator_storage
	 */
	private $translator_storage = null;

	/**
	 * Translator_Extractor
	 *
	 * @acess private
	 * @var Translator_Extractor $translator_extractor
	 */
	private $translator_extractor = null;

	/**
	 * Translator_Service
	 *
	 * @acess private
	 * @var Translator_Service $translator_service
	 */
	private $translator_service = null;

	/**
	 * Languages
	 *
	 * @access private
	 * @var array $languages
	 */
	private $languages = [];

	/**
	 * Translators
	 *
	 * @access private
	 * @var array $translators
	 */
	private static $translators = [];

	/**
	 * Constructor
	 *
	 * @acess public
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * Save
	 *
	 * @access public
	 */
	public function save() {
		if (empty($this->name)) {
			throw new \Exception('Cannot save translator, no name set');
		}
		self::$translators[$this->name] = $this;
	}

	/**
	 * Set translator_storage
	 *
	 * @access public
	 * @param Translator_Storage $translator_storage
	 */
	public function set_translator_storage(\Skeleton\I18n\Translator\Storage $translator_storage) {
		$this->translator_storage = $translator_storage;
	}

	/**
	 * Get translator_storage
	 *
	 * @access public
	 * @return Translator_Storage $translator_storage
	 */
	public function get_translator_storage() {
		return $this->translator_storage;
	}

	/**
	 * Set translator_extractor
	 *
	 * @access public
	 * @param Translator_Extractor $translator_extractor
	 */
	public function set_translator_extractor(\Skeleton\I18n\Translator\Extractor $translator_extractor) {
		$this->translator_extractor = $translator_extractor;
	}

	/**
	 * Set translator_service
	 *
	 * @access public
	 * @param Translator_Extractor $translator_extractor
	 */
	public function set_translator_service(\Skeleton\I18n\Translator\Service $translator_service) {
		$this->translator_service = $translator_service;
	}

	/**
	 * Get translator_extractor
	 *
	 * @access public
	 * @return Translator_Extractor $translator_extractor
	 */
	public function get_translator_extractor() {
		return $this->translator_extractor;
	}

	/**
	 * Set translator_service
	 *
	 * @access public
	 * @return Translator_Service $translator_service
	 */
	public function get_translator_service() {
		return $this->translator_service;
	}

	/**
	 * Get translation
	 *
	 * @access public
	 */
	public function get_translation(\Skeleton\I18n\LanguageInterface $language) {
		$translation = new Translation();
		$translator_storage = $this->translator_storage;
		$translator_storage->set_language($language);
		$translator_storage->set_name($this->name);
		$translator_storage->open();
		$translation->translator_storage = $translator_storage;
		$translation->language = $language;
		return $translation;
	}

	/**
	 * Get the name
	 *
	 * @access public
	 * @return string $name
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get by name
	 *
	 * @access public
	 * @param string $name
	 * @return \Skeleton\I18n\Translator $translator
	 */
	public static function get_by_name($name) {
		if (!isset(self::$translators[$name])) {
			throw new \Exception('No translator found for name "' . $name . '"');
		}
		return self::$translators[$name];
	}

	/**
	 * Get by name
	 *
	 * @access public
	 * @return \Skeleton\I18n\Translator[] $translators
	 */
	public static function get_all() {
		return self::$translators;
	}
}
