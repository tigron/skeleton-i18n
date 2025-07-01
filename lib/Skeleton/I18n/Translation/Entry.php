<?php

namespace Skeleton\I18n\Translation;

/**
 * Translation Entry class
 *
 * @author Roan Buysse <roan@tigron.be>
 */

class Entry {
	/**
	 * Source
	 *
	 * @acess public
	 * @var string $source
	 */
	public $source = '';

	/**
	 * Destination => the translated string
	 *
	 * @acess public
	 * @var string $destination
	 */
	private $destination = '';

	/**
	 * Fuzzy
	 *
	 * @acess public
	 * @var bool $fuzzy
	 */
	private $fuzzy = false;

	/**
	 * Constructor
	 *
	 * @acess public
	 * @param string $name
	 */
	public function __construct($name) {
		$this->source = $name;
	}

	/**
	 * Get the translation
	 *
	 * @return string
	 *
	 */
	public function get_translation(): string {
		return $this->destination;
	}

	/**
	 * @return bool
	 */
	public function is_translated(): bool {
		if (empty($this->destination) === true) {
			return false;
		}

		return true;
	}

	/**
	 * Is the translation fuzzy
	 *
	 * @return bool
	 */
	public function is_fuzzy(): bool {
		if ((bool)$this->fuzzy === true) {
			return true;
		}

		return false;
	}

	/**
	 * Set a singular translation
	 *
	 * @param string $translated
	 * @param bool $fuzzy
	 * @return void
	 */
	public function set(string $translated, bool $fuzzy = false): void {
		$this->destination = stripcslashes($translated);
		$this->fuzzy = $fuzzy;
	}

	/**
	 * Set a plural translation
	 *
	 * @param array $plurals
	 * @param bool $fuzzy
	 * @return void
	 */
	public function set_plural(array $plurals, bool $fuzzy = false): void {
		$this->destination = stripcslashes(implode('|', $plurals));
		$this->fuzzy = $fuzzy;
	}
}
