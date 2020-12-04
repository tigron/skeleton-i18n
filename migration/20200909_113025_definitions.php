<?php
/**
 * Database migration class
 */

namespace Skeleton\I18n;

use \Skeleton\Database\Database;

class Migration_20200909_113025_Definitions extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		// Make sure English is defined, if the table contains no records yet
		$languages = $db->get_all('SELECT * FROM language');

		if (count($languages) == 0) {
			$db->query("
				INSERT INTO `language` VALUES (1,'English','English','en','en_US');
			", []);
		}

		// Drop the obsolete name_ogone column
			$db->query("
				ALTER TABLE `language` DROP `name_ogone`;
			", []);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {}
}
