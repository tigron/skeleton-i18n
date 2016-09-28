<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * Code is based on Symfony/translation package (Fabien Potencier)
 * https://github.com/symfony/translation
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\I18n;

class Util {

	/**
	 * Load PO File
	 *
	 * @param string $filename
	 * @return array $strings
	 */
	public static function load($filename) {
		$strings = [];
		if (!file_exists($filename)) {
			return [];
		}
		$content = file_get_contents($filename);
		$lines = explode("\n", $content);

		$defaults = [
				   'ids' => [],
				   'translated' => null,
		];

		$item = $defaults;
		$flags = [];

		foreach ($lines as $line) {
		    $line = trim($line);
		    if ($line === '') {
		        // Whitespace indicated current item is done
		        if (!in_array('fuzzy', $flags)) {
		            self::add_message($strings, $item);
		        }
		        $item = $defaults;
		        $flags = array();
		    } elseif (substr($line, 0, 2) === '#,') {
		        $flags = array_map('trim', explode(',', substr($line, 2)));
		    } elseif (substr($line, 0, 7) === 'msgid "') {
		        // We start a new msg so save previous
		        // TODO: this fails when comments or contexts are added
		        self::add_message($strings, $item);
		        $item = $defaults;
		        $item['ids']['singular'] = substr($line, 7, -1);
		    } elseif (substr($line, 0, 8) === 'msgstr "') {
		        $item['translated'] = substr($line, 8, -1);
		    } elseif ($line[0] === '"') {
		        $continues = isset($item['translated']) ? 'translated' : 'ids';
		        if (is_array($item[$continues])) {
		            end($item[$continues]);
		            $item[$continues][key($item[$continues])] .= substr($line, 1, -1);
		        } else {
		            $item[$continues] .= substr($line, 1, -1);
		        }
		    } elseif (substr($line, 0, 14) === 'msgid_plural "') {
		        $item['ids']['plural'] = substr($line, 14, -1);
		    } elseif (substr($line, 0, 7) === 'msgstr[') {
		        $size = strpos($line, ']');
		        $item['translated'][(int) substr($line, 7, 1)] = substr($line, $size + 3, -1);
		    }
		}
		// save last item
		if (!in_array('fuzzy', $flags)) {
		    self::add_message($strings, $item);
		}

		return $strings;
	}

	/**
	 * Prepare a string for loading
	 *
	 * @access private
	 * @param string $string
	 * @return string $fixed_string
	 */
	public static function prepare_load_string($string) {
		$smap = ['/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\\"/'];
		$rmap = ['', "\n", "\r", "\t", '"'];
		return (string) preg_replace($smap, $rmap, $string);
	}

	/**
	 * Prepare a string to be written in a po file
	 *
	 * @access private
	 * @param string $string
	 * @return string $fixed_string
	 */
	public static function prepare_save_string($string) {
		$smap = ['"', "\n", "\t", "\r"];
		$rmap = ['\\"', '\\n"' . "\n" . '"', '\\t', '\\r'];
		return (string) str_replace($smap, $rmap, $string);
	}

	/**
	 * Save a po file, based on a translation array
	 *
	 * @access public
	 * @param string $filename
	 * @param array $strings
	 */
	public static function save($filename, $project, $language, $strings) {
		ksort($strings, SORT_STRING | SORT_FLAG_CASE);
		$dir = dirname($filename);
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		// Guess the language string
		switch ($language->name_short) {
			case 'de':
			case 'es':
			case 'fr':
			case 'nl':
			case 'it':
			case 'fi':
				$language_string = $language->name_short;
				break;
			case 'se':
				$language_string = 'sv';
				break;
			case 'zh-chs':
				$language_string = 'zh_Hans';
				break;
			default:
				$language_string = 'unknown';
		}

		$output = '';

		// Generate the file header
		$output .= '# .po file for project "' . $project . '" in ' . $language->name . "\n";
		$output .= '' . "\n";
		$output .= 'msgid ""' . "\n";
		$output .= 'msgstr ""' . "\n";
		$output .= '"Project-Id-Version: ' . $project . '-' . $language_string . '.' . time() . '\n"' . "\n";
		$output .= '"PO-Revision-Date: ' . date('Y-m-d H:iO')  . '\n"' . "\n";
		$output .= '"MIME-Version: 1.0\n"' . "\n";
		$output .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
		$output .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
		$output .= '"Language: ' . $language_string . '\n"' . "\n";

		$output .= "\n";

		foreach ($strings as $key => $value) {
			$output .= 'msgid "' . self::prepare_save_string($key) . '"' . "\n";
			$output .= 'msgstr "' . self::prepare_save_string($value) . '"' . "\n\n";
		}

		file_put_contents($filename, $output);
	}

	/**
	 * Merge 2 po files
	 *
	 * @access public
	 * @param array $strings1
	 * @param array $strings2
	 */
	public static function merge($base, $extra) {
		$base_strings = self::load($base);
		$extra_strings = self::load($extra);

		$extra_strings_keys = array_keys($extra_strings);
		foreach ($extra_strings_keys as $string) {
			if (isset($base_strings[$string]) AND $base_strings[$string] != '') {
				$extra_strings[$string] = $base_strings[$string];
			}
		}

		self::save($base, $extra_strings);
	}

	private static function add_message(array &$strings, array $item) {
        if (is_array($item['translated'])) {
            $strings[stripcslashes($item['ids']['singular'])] = stripcslashes($item['translated'][0]);
            if (isset($item['ids']['plural'])) {
                $plurals = $item['translated'];
                // PO are by definition indexed so sort by index.
                ksort($plurals);
                // Make sure every index is filled.
                end($plurals);
                $count = key($plurals);
                // Fill missing spots with '-'.
                $empties = array_fill(0, $count + 1, '-');
                $plurals += $empties;
                ksort($plurals);
                $strings[stripcslashes($item['ids']['plural'])] = stripcslashes(implode('|', $plurals));
            }
        } elseif (!empty($item['ids']['singular'])) {
            $strings[stripcslashes($item['ids']['singular'])] = stripcslashes($item['translated']);
        }
    }
}
