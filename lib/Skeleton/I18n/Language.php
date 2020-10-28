<?php
/**
 * Language class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\I18n;

use \Skeleton\Database\Database;

class Language implements LanguageInterface {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Cache;

	/**
	 * Language
	 *
	 * @var Language $language
	 * @access private
	 */
	private static $language = null;

	/**
	 * Get by name_short
	 *
	 * @access public
	 * @return Language
	 * @param string $name_short
	 */
	public static function get_by_name_short($name) {
		if (self::trait_cache_enabled()) {
			try {
				$object = self::cache_get(get_class() . '_' . $name);
				return $object;
			} catch (\Exception $e) {}
		}

		$db = Database::Get();
		$id = $db->get_one('SELECT id FROM language WHERE name_short=?', [$name]);

		if ($id === null) {
			throw new \Exception('No such language');
		}

		$classname = Config::$language_interface;
		return $classname::get_by_id($id);
	}

	/**
	 * Detect the language based on the HTTP_ACCEPT_LANGUAGE header
	 *
	 * @access public
	 * @return LanguageInterface $language
	 */
	public static function detect() {
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			throw new \Exception('Language cannot be detected, no HTTP_ACCEPT_LANGUAGE header set');
		}

		$languages = self::get_all();
		$available_languages = [];
		foreach ($languages as $language) {
			$available_languages[] = $language->name_short;
		}

		$language = Util::get_best_matching_language($_SERVER['HTTP_ACCEPT_LANGUAGE'], $available_languages);
		if ($language === false) {
			throw new \Exception('No matching language found');
		}

		return self::get_by_name_short($language);
	}

	/**
	 * Set the current language
	 *
	 * @access public
	 * @param Language $language
	 */
	public static function set(Language $language) {
		self::$language = $language;
	}

	/**
	 * Get the currect language
	 *
	 * @access public
	 * @return Language $language
	 */
	public static function get() {
		return self::$language;
	}

}
