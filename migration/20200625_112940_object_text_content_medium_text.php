<?php
/**
 * Database migration class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */
namespace Skeleton\I18n;


use \Skeleton\Database\Database;

class Migration_20200625_112940_Object_text_content_medium_text extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("ALTER TABLE `object_text`
					CHANGE `content` `content` mediumtext COLLATE 'utf8_unicode_ci' NOT NULL AFTER `label`;");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
