<?php
/**
 * Language class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\I18n;

interface LanguageInterface {

	/**
	 * is translatable
	 *
	 * @access public
	 * @return bool $translatable
	 */
	public function is_translatable(): bool;

	/**
	 * Get by name_short
	 *
	 * @access public
	 * @return Language
	 * @param string $name_short
	 */
	public static function get_by_name_short($name);

	/**
	 * Get all languages
	 *
	 * @access public
	 * @return LanguageInterface[] $languages
	 */
	public static function get_all();

}
