<?php
/**
 * Service
 *
 * @author Roan Buysse <roan@tigron.be>
 */
namespace Skeleton\I18n\Translator;

abstract class Service extends \GuzzleHttp\Client {

	/**
	 * Returns a translation entry object with the destination (translation)
	 *
	 * @param \Skeleton\I18n\Translation\Entry $entry
	 * @return \Skeleton\I18n\Translation\Entry
	 */
	abstract public function translate(\Skeleton\I18n\Translation\Entry $entry): \Skeleton\I18n\Translation\Entry;
}
