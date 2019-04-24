<?php
/**
 * Database migration class
 *
 */
namespace Skeleton\I18n;


use \Skeleton\Database\Database;

class Migration_20190424_140257_Created_default_value extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("ALTER TABLE `object_text` MODIFY `created` datetime NOT NULL DEFAULT NOW()");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
