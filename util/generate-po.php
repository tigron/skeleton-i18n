<?php
/**
 * This tool generates the .po files for each application.
 * The resulting files can be found in $ROOT/po/
 *
 * You can manually add paths to be translated by defining a variable
 * $paths which should be an array with the name of the application
 * as the key, and the path to the application as the value.
 *
 * Example:
 *
 * $paths['email'] = '/path/to/email/template';
 * $paths['pdf'] = '/path/to/pdf/template';
 */

namespace Skeleton\I18n;

// Fetch paths for Applications
$applications = \Skeleton\Core\Application::get_all();

// If the paths array hasn't been defined yet, make sure it exists
if (!isset($paths) or !is_array($paths)) {
	$paths = [];
}

foreach ($applications as $application) {
	$paths[$application->name] = $application->path;
}

// Translate all the applications
foreach ($paths as $application => $directory) {
	translate_application($application, $directory);
}

/**
 * Translate an application
 *
 * @param string $application Name of the application
 * @param string $directory Application path
 */
function translate_application($application, $directory) {
	echo 'translating ' . $application . ' (' . $directory . ')';

	// Fetch the templates in this directory
	$templates = get_templates($directory);
	$strings = [];

	// Parse all the files we found
	foreach ($templates as $template) {
		$strings = array_merge($strings, get_strings($template));
	}

	// Translate the strings
	$languages = Language::get_all();
	foreach ($languages as $language) {
		// Don't create a .po file if it is our base_language
		if ($language->name_short == Config::$base_language) {
			continue;
		}

		echo ' ' . $language->name_short;

		// If we already have a (partially) translated file, merge
		if (file_exists(Config::$po_directory . '/' . $language->name_short . '/' . $application . '.po')) {
			$translated = Util::load(Config::$po_directory . '/' . $language->name_short . '/' . $application . '.po');
			$old_translated = Util::load(Config::$po_directory . '/' . $language->name_short . '.po');
			$translated = array_merge($translated, $old_translated);
		} else {
			$translated = [];
		}

		// Create a new array with the merged translations
		$new_po = [];
		foreach ($strings as $string) {
			if (isset($translated[$string]) and $translated[$string] != '') {
				$new_po[$string] = $translated[$string];
			} else {
				$new_po[$string] = '';
			}
		}

		// Stop doing what we are doing if there are no strings anyway
		if (count($new_po) == 0) {
			continue;
		}

		// And save!
		Util::save(Config::$po_directory . '/' . $language->name_short . '/' . $application . '.po', $application, $language, $new_po);
	}

	echo "\n";
}

/**
 * Parse all translatable stings out of a file
 *
 * @param string $file The full path of the file to parse
 */
function get_strings($file) {
	$content = file_get_contents($file);

	/**
	 * {% trans "string" %}
	 */
	preg_match_all("/\{%\s*trans \"(.*?)\"\s*%\}/", $content, $matches);
	$twig_strings = unescape_strings($matches[1], '"');

	/**
	 * {% trans 'string' %}
	 */
	preg_match_all("/\{%\s*trans '(.*?)'\s*%\}/", $content, $matches);
	$twig_strings2 = unescape_strings($matches[1], '\'');

	/**
	 * 'string'|trans
	 */
	preg_match_all('/\'((?:[^\'\\\\]|\\\\.)*)\'\|trans/', $content, $matches);
	$twig_strings3 = unescape_strings($matches[1], '\'');

	/**
	 * "string"|trans
	 */
	preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"\|trans/', $content, $matches);
	$twig_strings4 = unescape_strings($matches[1], '"');

	/**
	 * {% trans %}string{% endtrans %}
	 */
	preg_match_all("/\{% trans %\}(.*?)\{% endtrans %\}/s", $content, $matches);
	$twig_strings5 = $matches[1];

	/**
	 * Translation::translate('string')
	 */
	preg_match_all("/Translation\:\:translate\(\"(.*?)\"\)/", $content, $matches);
	$module_strings = unescape_strings($matches[1], '\'');

	return array_merge($twig_strings, $twig_strings2, $twig_strings3, $twig_strings4, $twig_strings5, $module_strings);
}

/**
 * Unescape strings in an array
 *
 * @param array $strings
 * @param string $escape
 */
function unescape_strings($strings, $escape) {
	if (strlen($escape) <> 1) {
		throw new Exception('Escape parameter can only be one character');
	}

	$escaped_strings = [];
	foreach ($strings as $string) {
		$escaped_strings[] = (string) str_replace('\\' . $escape, $escape, $string);
	}

	return $escaped_strings;
}

/**
 * Find all template files in a given directory
 *
 * @param string $directory Directory to search for templates
 */
function get_templates($directory) {
	// Get all files
	$files = scandir($directory);

	// Loop over all the files, recurse if it is a directory
	$templates = [];
	foreach ($files as $file) {
		if ($file[0] == '.') {
			continue;
		}

		// If it is a directory, recurse
		if (is_dir($directory . '/' . $file)) {
			$dir_templates = get_templates($directory . '/' . $file);
			foreach ($dir_templates as $dir_template) {
				$templates[] = $dir_template;
			}
			continue;
		}

		// If it is a file that we support, add it to the result
		if (strpos($file, '.') !== false) {
			$file_parts = explode('.', $file);
			$extension = array_pop($file_parts);
			if ($extension == 'twig' OR $extension == 'tpl' OR $extension == 'php') {
				$templates[] = $directory . '/' . $file;
			}
		}
	}

	return $templates;
}