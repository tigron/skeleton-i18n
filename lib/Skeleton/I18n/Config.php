<?php
/**
 * Config class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\I18n;

class Config {

	/**
	 * Cache directory
	 *
	 * This folder will be used to store the cached translations
	 *
	 * @access public
	 * @var string $cache_directory
	 */
	public static $cache_directory = '/tmp';

	/**
	 * Directory where we will store the generated .po files
	 *
	 * @access public
	 * @var string $po_directory
	 */
	public static $po_directory = '/tmp';

	/**
	 * Base language, the language in which the templates are written
	 *
	 * @access public
	 * @var string $base_language
	 */
	public static $base_language = 'en';

	/**
	 * Enable debugging
	 *
	 * @access public
	 * @var bool $debug
	 */
	public static $debug = false;
}
