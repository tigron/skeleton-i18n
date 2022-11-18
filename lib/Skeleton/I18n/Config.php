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
	 * Cache path
	 *
	 * This folder will be used to store the cached translations
	 *
	 * @access public
	 * @var string $cache_path
	 */
	public static $cache_path = '/tmp';

	/**
	 * Base language, the language in which the templates are written
	 *
	 * @access public
	 * @var string $base_language
	 */
	public static $base_language = 'en';

	/**
	 * Language interface class
	 *
	 * This class will provide the Language functionality, by default a class is defined
	 *
	 * @access public
	 * @var string $language_interface
	 */
	public static $language_interface = '\Skeleton\I18n\Language';

	/**
	 * Enable debugging
	 *
	 * @access public
	 * @var bool $debug
	 */
	public static $debug = true;

	/**
	 * Should the po be prefilled when requesting a new string
	 *
	 * @access public
	 * @var bool $auto_fill_po
	 */
	public static $auto_fill_po = false;

	/**
	 * Should object text be auto created when it does not exist
	 *
	 * @access public
	 * @var bool $auto_create
	 */
	public static $auto_create = true;
}
