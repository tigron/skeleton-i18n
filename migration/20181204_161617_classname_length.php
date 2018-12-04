<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\I18n;

use \Skeleton\Database\Database;

class Migration_20181204_161617_Classname_length extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `object_text`
			CHANGE `classname` `classname` varchar(64) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`;
		", []);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
