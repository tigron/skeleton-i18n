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
	 * Translation
	 *
	 * @access private
	 * @var Translation $translation
	 */
	private static $translation = null;

	/**
	 * Language
	 *
	 * @access public
	 * @var Language $language
	 */
	public $language = null;

	/**
	 * Application
	 *
	 * @access private
	 * @var string 	$application
	 */
	private $application_name = null;

	/**
	 * Strings
	 *
	 * @access private
	 * @var array $strings
	 */
	private $strings = [];

	/**
	 * Constructor
	 *
	 * @access public
	 * @param Language $language
	 * @param string $application
	 */
	public function __construct(LanguageInterface $language = null, $application_name = null) {
		if ($language === null AND $application_name === null) {
			$this->language = \Application::get()->language;
			$this->application_name = \Application::get()->name;
		} else {
			$this->language = $language;
			$this->application_name = $application_name;
		}

		$this->reload_po_file();
		$this->load_strings();
	}

	/**
	 * Translate a string
	 *
	 * @access public
	 * @param string $string
	 * @return string $string
	 */
	public function translate_string($string) {
		if ($this->language->name_short == Config::$base_language) {
			return $string;
		}

		if (!isset($this->strings[$string])) {
			$this->add_to_po($string);
		}

		if ($this->strings[$string] == '') {
			if (Config::$debug) {
				return '[NT]' . $string;
			} else {
				return $string;
			}
		}

		return $this->strings[$string];
	}

	/**
	 * Add a string to the po file
	 *
	 * @access public
	 * @param string $string
	 */
	private function add_to_po($string) {
		$this->strings[$string] = '';

		$current_strings = Util::load(Config::$po_directory . '/' . $this->language->name_short . '/' . $this->application_name . '.po');
		$untranslated = [$string => ''];
		$strings = array_merge($untranslated, $current_strings);
		ksort($strings);

		Util::save(Config::$po_directory . '/' . $this->language->name_short . '/' . $this->application_name . '.po', $this->application_name, $this->language, $strings);
	}

	/**
	 * Read the po files
	 *
	 * @access public
	 */
	private function reload_po_file() {
		$po_files = [];
		$po_files[] = Config::$po_directory . '/' . $this->language->name_short . '/' . $this->application_name . '.po';
		$packages = \Skeleton\Core\Package::get_all();

		foreach ($packages as $package) {
			if (file_exists(Config::$po_directory . '/' . $this->language->name_short . '/package/' . $package->name . '.po')) {
				$po_files[] = Config::$po_directory . '/' . $this->language->name_short . '/package/' . $package->name . '.po';
			}
		}

		$array_modified = 0;
		if (file_exists(Config::$cache_directory . '/' . $this->language->name_short . '/' . $this->application_name . '.php')) {
			$array_modified = filemtime(Config::$cache_directory . '/' . $this->language->name_short . '/' . $this->application_name . '.php');
		}

		$po_file_modified = null;
		foreach ($po_files as $po_file) {
			if (!file_exists($po_file)) {
				continue;
			}
			if ($po_file_modified === null) {
				$po_file_modified = filemtime($po_file);
			}
			if (filemtime($po_file) > $po_file_modified) {
				$po_file_modified = filemtime($po_file);
			}
		}

		if ($array_modified >= $po_file_modified) {
			return;
		}

		$po_strings = [];
		foreach (array_reverse($po_files) as $po_file) {
			$strings = Util::load($po_file);
			foreach ($strings as $key => $value) {
				if (!isset($po_strings[$key])) {
					$po_strings[$key] = $value;
				} elseif ($value != '') {
					$po_strings[$key] = $value;
				} else {
					continue;
				}
			}
		}

		if (!file_exists(Config::$cache_directory . '/' . $this->language->name_short)) {
			mkdir(Config::$cache_directory . '/' . $this->language->name_short, 0755, true);
		}

		file_put_contents(Config::$cache_directory . '/' . $this->language->name_short . '/' . $this->application_name . '.php', '<?php $strings = ' . var_export($po_strings, true) . ';');
	}

	/**
	 * Load the strings
	 *
	 * @access private
	 */
	private function load_strings() {
		if (file_exists(Config::$cache_directory . '/' . $this->language->name_short . '/' . $this->application_name . '.php')) {
			require Config::$cache_directory . '/' . $this->language->name_short . '/' . $this->application_name . '.php';
			$this->strings = $strings;
		}
	}

	/**
	 * Get a translation object
	 *
	 * @access public
	 * @return Translation $translation
	 */
	public static function get(LanguageInterface $language = null, $application_name = null) {
		if (!isset(self::$translation[$language->name_short]) OR self::$translation[$language->name_short]->application_name != $application_name) {
			self::$translation[$language->name_short] = new self($language, $application_name);
		}

		return self::$translation[$language->name_short];
	}

	/**
	 * Translate a string
	 *
	 * @access public
	 * @return string $translated_string
	 * @param string $string
	 */
	public static function translate($string, Translation $translation = null) {
		if ($translation !== null) {
			$translation = self::get($translation->language, $translation->application_name);
		} else {
			$translation = self::get(\Application::get()->language, \Application::get()->name);
		}

		return $translation->translate_string($string);
	}

	/**
	 * Translate a plural string
	 *
	 * @access public
	 * @return string $translated_string
	 * @param string $string
	 */
	public static function translate_plural($string, Translation $translation = null) {
		if ($translation !== null) {
			$translation = self::get($translation->language, $translation->application_name);
		} else {
			$translation = self::get();
		}

		return $translation->translate_string($string);
	}
}
