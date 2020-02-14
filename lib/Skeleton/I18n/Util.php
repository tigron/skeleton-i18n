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

	/**
	 * Get best matching language
	 *
	 * Borrowed from Drupal (https://github.com/drupal/core-utility)
	 *
	 * @access public
	 * @param string $http_accept_language
	 * @param array $langcodes
	 * @param array $mappings
	 * @return string $language
	 */
	public static function get_best_matching_language($http_accept_language, $langcodes, $mappings = []) {
		// The Accept-Language header contains information about the language
		// preferences configured in the user's user agent / operating system.
		// RFC 2616 (section 14.4) defines the Accept-Language header as follows:
		//		Accept-Language = "Accept-Language" ":"
		//						1#( language-range [ ";" "q" "=" qvalue ] )
		//		language-range	= ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" )
		// Samples: "hu, en-us;q=0.66, en;q=0.33", "hu,en-us;q=0.5"
		$ua_langcodes = [];
		if (preg_match_all('@(?<=[, ]|^)([a-zA-Z-]+|\\*)(?:;q=([0-9.]+))?(?:$|\\s*,\\s*)@', trim($http_accept_language), $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				if ($mappings) {
					$langcode = strtolower($match[1]);
					foreach ($mappings as $ua_langcode => $standard_langcode) {
						if ($langcode == $ua_langcode) {
							$match[1] = $standard_langcode;
						}
					}
				}

				// We can safely use strtolower() here, tags are ASCII.
				// RFC2616 mandates that the decimal part is no more than three digits,
				// so we multiply the qvalue by 1000 to avoid floating point
				// comparisons.
				$langcode = strtolower($match[1]);
				$qvalue = isset($match[2]) ? (double) $match[2] : 1;

				// Take the highest qvalue for this langcode. Although the request
				// supposedly contains unique langcodes, our mapping possibly resolves
				// to the same langcode for different qvalues. Keep the highest.
				$ua_langcodes[$langcode] = max((int) ($qvalue * 1000), isset($ua_langcodes[$langcode]) ? $ua_langcodes[$langcode] : 0);
			}
		}

		// We should take pristine values from the HTTP headers, but Internet
		// Explorer from version 7 sends only specific language tags (eg. fr-CA)
		// without the corresponding generic tag (fr) unless explicitly configured.
		// In that case, we assume that the lowest value of the specific tags is the
		// value of the generic language to be as close to the HTTP 1.1 spec as
		// possible.
		// See http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4 and
		// http://blogs.msdn.com/b/ie/archive/2006/10/17/accept-language-header-for-internet-explorer-7.aspx
		asort($ua_langcodes);
		foreach ($ua_langcodes as $langcode => $qvalue) {

			// For Chinese languages the generic tag is either zh-hans or zh-hant, so
			// we need to handle this separately, we can not split $langcode on the
			// first occurrence of '-' otherwise we get a non-existing language zh.
			// All other languages use a langcode without a '-', so we can safely
			// split on the first occurrence of it.
			if (strlen($langcode) > 7 && (substr($langcode, 0, 7) == 'zh-hant' || substr($langcode, 0, 7) == 'zh-hans')) {
				$generic_tag = substr($langcode, 0, 7);
			} else {
				$generic_tag = strtok($langcode, '-');
			}

			if (!empty($generic_tag) && !isset($ua_langcodes[$generic_tag])) {
				// Add the generic langcode, but make sure it has a lower qvalue as the
				// more specific one, so the more specific one gets selected if it's
				// defined by both the user agent and us.
				$ua_langcodes[$generic_tag] = $qvalue - 0.1;
			}
		}

		// Find the added language with the greatest qvalue, following the rules
		// of RFC 2616 (section 14.4). If several languages have the same qvalue,
		// prefer the one with the greatest weight.
		$best_match_langcode = FALSE;
		$max_qvalue = 0;
		foreach ($langcodes as $langcode_case_sensitive) {

			// Language tags are case insensitive (RFC2616, sec 3.10).
			$langcode = strtolower($langcode_case_sensitive);

			// If nothing matches below, the default qvalue is the one of the wildcard
			// language, if set, or is 0 (which will never match).
			$qvalue = isset($ua_langcodes['*']) ? $ua_langcodes['*'] : 0;

			// Find the longest possible prefix of the user agent supplied language
			// ('the language-range') that matches this site language ('the language
			// tag').
			$prefix = $langcode;
			do {
				if (isset($ua_langcodes[$prefix])) {
					$qvalue = $ua_langcodes[$prefix];
					break;
				}
			} while ($prefix = substr($prefix, 0, strrpos($prefix, '-')));

			// Find the best match.
			if ($qvalue > $max_qvalue) {
				$best_match_langcode = $langcode_case_sensitive;
				$max_qvalue = $qvalue;
			}
		}

		return $best_match_langcode;
	}

	/**
	 * Add message
	 *
	 * @access private
	 * @param array $strings
	 * @param array $item
	 */
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
