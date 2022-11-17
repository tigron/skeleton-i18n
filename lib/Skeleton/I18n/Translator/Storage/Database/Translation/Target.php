<?php
/**
 * Translator Storage Database Translation class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\I18n\Translator\Storage\Database\Translation;

use \Skeleton\Database\Database;

class Target {
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
		'database_table' => 'translation_target'
	];

	/**
	 * get by source language
	 *
	 * @access public
	 * @param Source $source
	 * @param Language $language
	 * @return Translation
	 */
	public static function get_by_source_language(Source $source, \Skeleton\I18n\Language $language) {
		$db = Database::get();
		$id = $db->get_one("SELECT id FROM translation_target WHERE translation_source_id = ? AND language_id = ?", [ $source->id, $language->id ]);
		if ($id == null) {
			throw new \Exception("No translation found");
		}
		return self::get_by_id($id);
	}
}