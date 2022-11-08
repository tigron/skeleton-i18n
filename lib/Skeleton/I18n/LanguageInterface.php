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
	 * Get by name_short
	 *
	 * @access public
	 * @return Language
	 * @param string $name_short
	 */
	public static function get_by_name_short($name);

	/**
	 * Detect the language based on the HTTP_ACCEPT_LANGUAGE header
	 *
	 * @access public
	 * @return LanguageInterface $language
	 */
	public static function detect();

	/**
	 * Get all languages
	 *
	 * @access public
	 * @return LanguageInterface[] $languages
	 */
	public static function get_all();

}
