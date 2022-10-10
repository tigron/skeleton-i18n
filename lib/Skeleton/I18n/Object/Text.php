<?php
/**
 * Object_Text class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\I18n\Object;
use Skeleton\Database\Database;

class Text {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Delete;
	use \Skeleton\Object\Cache;

	/**
	 * class_configuration
	 *
	 * @access private
	 */
	private static $class_configuration = [
		'database_table' => 'object_text'
	];

	/**
	 * Set a detail
	 *
	 * @access public
	 * @param string $key
	 * @param mixex $value
	 */
	public function __set($key, $value) {
		if ($key == 'object') {
			$class_parents = class_parents($value);
			if ($class_parents === false || count($class_parents) == 0) {
				$classname = get_class($value);
			} else {
				$classname = array_pop($class_parents);
			}
			$this->details['classname'] = $classname;
			if ($value->id === null) {
				$value->save();
			}
			$this->details['object_id'] = $value->id;
		} else {
			$this->details[$key] = $value;
		}
	}

	/**
	 * Get by object
	 *
	 * @access public
	 * @param mixed $object
	 */
	public static function get_by_object($object) {
		$db = self::trait_get_database();
		$class = get_class($object);
		$data = $db->get_all('SELECT * FROM object_text WHERE classname=? AND object_id=?', [ $class, $object->id ]);

		$object_texts = [];
		foreach ($data as $details) {
			$object_text = new self();
			$object_text->id = $details['id'];
			$object_text->details = $details;

			$object_texts[] = $object_text;
		}

		return $object_texts;
	}

	/**
	 * Get by object, label, language
	 *
	 * @access public
	 * @param mixed $object
	 * @param string $label
	 * @param Language $language
	 */
	public static function get_by_object_label_language($object, $label, \Skeleton\I18n\LanguageInterface $language) {
		$class_parents = class_parents($object);
		if ($class_parents === false || count($class_parents) == 0) {
			$class = get_class($object);
		} else {
			$class = array_pop($class_parents);
		}

		if (self::trait_cache_enabled()) {
			try {
				$object = self::cache_get($class . '_' . $object->id . '_' . $label . '_' . $language->name_short);
				return $object;
			} catch (\Exception $e) {}
		}

		$db = self::trait_get_database();
		$data = $db->get_row('SELECT * FROM object_text WHERE classname=? AND object_id=? AND label=? AND language_id=?', [ $class, $object->id, $label, $language->id ]);

		if ($data === null) {
			if (!\Skeleton\I18n\Config::$auto_create) {
				return null;
			} else {
				$requested = new self();
				$requested->object = $object;
				$requested->language_id = $language->id;
				$requested->label = $label;
				$requested->content = '';
				$requested->save();

				if (self::trait_cache_enabled()) {
					self::cache_set(self::trait_get_cache_key($requested), $requested);
				}

				return $requested;
			}
		}

		$object_text = new self();
		$object_text->id = $data['id'];
		$object_text->details = $data;

		if (self::trait_cache_enabled()) {
			self::cache_set(self::trait_get_cache_key($object_text), $object_text);
		}

		return $object_text;
	}

	/**
	 * Get for object classname
	 *
	 * @access public
	 * @param string $classname
	 * @return array $object_texts
	 */
	public static function get_by_object_classname($classname) {
		$db = self::trait_get_database();
		$ids = $db->get_column('SELECT id FROM object_text WHERE classname=?', [ $classname ]);

		$object_texts = [];
		foreach ($ids as $id) {
			$object_texts[] = self::get_by_id($id);
		}

		return $object_texts;
	}

	/**
	 * Get for object classname
	 *
	 * @access public
	 * @param string $classname
	 * @return array $object_texts
	 */
	public static function get_by_classname_language($classname, \Skeleton\I18n\Language $language) {
		$db = self::trait_get_database();
		$ids = $db->get_column('SELECT id FROM object_text WHERE classname=? AND language_id=?', [ $classname, $language->id ]);

		$object_texts = [];
		foreach ($ids as $id) {
			$object_texts[] = self::get_by_id($id);
		}

		return $object_texts;
	}

	/**
	 * Get classnames
	 *
	 * @access public
	 * @return array $classnames
	 */
	public static function get_classnames() {
		$db = self::trait_get_database();
		return $db->get_column('SELECT DISTINCT(classname) FROM object_text', []);
	}

	/**
	 * Get cache key
	 *
	 * @access public
	 * @param mixed $object
	 * @return string $key
	 */
	public static function trait_get_cache_key($object) {
		return $object->classname . '_' . $object->object_id . '_' . $object->label . '_' . $object->language->name_short;
	}
}