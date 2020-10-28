<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */
namespace Skeleton\I18n;

class Migration_20201028_172051_Flush_cache extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		if (isset(\Skeleton\Object\Config::$cache_handler) && \Skeleton\Object\Config::$cache_handler != '') {
			\Skeleton\Object\Cache::cache_flush();
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
