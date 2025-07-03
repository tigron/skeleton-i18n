<?php
/**
 * Service
 *
 * @author Roan Buysse <roan@tigron.be>
 */
namespace Skeleton\I18n\Translator;

abstract class Service {
	/**
	 * Returns a translation entry object with the destination (translation)
	 *
	 * @param \Skeleton\I18n\Translation\Entry $entry
	 * @return \Skeleton\I18n\Translation\Entry
	 */
	abstract public function translate(\Skeleton\I18n\Translation\Entry $entry): \Skeleton\I18n\Translation\Entry;

	/**
	 * Set the source language
	 *
	 * @param \Language $language
	 * @return void
	 */
	abstract public function set_source_language(\Language $language): void;

	/**
	 * Set the target language
	 *
	 * @param \Language $language
	 * @return void
	 */
	abstract public function set_target_language(\Language $language): void;
}
