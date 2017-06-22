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

class Migration_20170519_155027_Init extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE IF NOT EXISTS `language` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `name_local` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `name_short` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `name_ogone` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id`),
			  FULLTEXT KEY `name_short` (`name_short`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
