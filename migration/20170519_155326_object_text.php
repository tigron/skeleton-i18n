<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
namespace Skeleton\I18n;

use \Skeleton\Database\Database;

class Migration_20170519_155326_Object_text extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE IF NOT EXISTS `object_text` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `object_id` int(11) unsigned NOT NULL,
			  `language_id` int(11) unsigned NOT NULL,
			  `classname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
			  `label` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
			  `content` text COLLATE utf8_unicode_ci NOT NULL,
			  `created` datetime NOT NULL,
			  `updated` datetime NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `classname` (`classname`),
			  KEY `object_id` (`object_id`),
			  KEY `language_id` (`language_id`),
			  KEY `label` (`label`),
			  KEY `classname_object_id_label` (`classname`,`object_id`,`label`),
			  KEY `classname_object_id_label_language_id` (`classname`,`object_id`,`label`,`language_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
