<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */
namespace Skeleton\I18n;

use \Skeleton\Database\Database;

class Migration_20180329_162509_Object_cache extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$classnames = $db->get_column('SELECT DISTINCT classname FROM object_text');
		foreach ($classnames as $classname) {
			$class_parents = class_parents($classname);
			if ($class_parents !== false && count($class_parents) > 0) {
				$class = array_pop($class_parents);
				$db->query("UPDATE object_text SET classname = ? WHERE classname = ?", [ $class, $classname ]);
			}
		}
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
