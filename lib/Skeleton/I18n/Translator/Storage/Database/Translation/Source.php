<?php
/**
 * Translator Storage Database Translation Source class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\I18n\Translator\Storage\Database\Translation;

use \Skeleton\Database\Database;

class Source {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * Class configuration
	 *
	 * @access private
	 * @var array $class_configuration
	 */
	private static $class_configuration = [
		'database_table' => 'translation_source'
	];

	/**
	 * get translation targets
	 *
	 * @access public
	 * @return Target[]
	 */
	public function get_translation_targets() {
		return Target::get_by_source($this);
	}

	/**
	 * get by name string
	 *
	 * @access public
	 * @param $name
	 * @param $string
	 * @return String
	 */
	public static function get_by_name_string($name, $string) {
		$db = Database::get();
		$id = $db->get_one("SELECT id FROM translation_source WHERE name = ? AND string = ?", [ $name, $string ]);
		if ($id == null) {
			throw new \Exception("Source not found");
		}
		return self::get_by_id($id);
	}
}