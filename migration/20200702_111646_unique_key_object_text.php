<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\I18n;

use \Skeleton\Database\Database;

class Migration_20200702_111646_Unique_key_object_text extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `object_text`
			ADD UNIQUE `classname_object_id_label_language_id` (`classname`, `object_id`, `label`, `language_id`),
			DROP INDEX `classname_object_id_label_language_id`;
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
