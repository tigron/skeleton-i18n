<?php
/**
 * Service Tas
 *
 * @author Roan Buysse <roan@tigron.be>
 */
namespace Skeleton\I18n\Translator\Service;

class Tas extends \Skeleton\I18n\Translator\Service {

	/**
	 * Endpoint
	 *
	 * @var $endpoint
	 */
	private $endpoint = 'https://655.mtis.workers.dev';

	/**
	 * Source Language
	 *
	 * @var $endpoint
	 */
	private $source_lang = '';

	/**
	 * Target Language
	 *
	 * @var $endpoint
	 */
	private $target_lang = '';

	/**
	 * Constructor
	 *
	 * @throws \RuntimeException
	 * @throws \LogicException
	 * @throws \GuzzleHttp\Exception\InvalidArgumentException
	 * @throws \InvalidArgumentException
	 */
	public function __construct() {
		try {
			$this->source_lang = (\Language::get_base()->name_short);
		} catch (\Exception $e) {
			$this->source_lang  = 'en';
		}

		parent::__construct([
			'base_uri' => $this->endpoint,
			'headers' => [
				'Content-Type' => 'application/json; charset=UTF8'
			],
		]);
	}


	/**
	 * Returns a translation entry object with the destination (translation)
	 *
	 * @param \Skeleton\I18n\Translation\Entry $entry
	 * @return \Skeleton\I18n\Translation\Entry
	 */
	public function translate(\Skeleton\I18n\Translation\Entry $entry): \Skeleton\I18n\Translation\Entry {
		$result = $this->translation_request($entry->source);
		$entry->set($result->response->translated_text, true);
		return $entry;
	}


	/**
	 * Set the source language
	 *
	 * @param \Language $language
	 * @return void
	 */
	public function set_source_language(\Language $language): void {
		$this->source_lang = $language->name_short;
	}

	/**
	 * Set the target language
	 *
	 * @param \Language $language
	 * @return void
	 */
	public function set_target_language(\Language $language): void {
		$this->target_lang = $language->name_short;
	}

	/**
	 * Set the edpoint for  a service
	 *
	 * @param \Language $language
	 * @return void
	 */
	public function set_endpoint(string $endpoint): void {
		$this->endpoint = $endpoint;
	}

	/**
	 * Translation request to the api
	 *
	 * @param string $text
	 * @return \stdClass
	 */
	private function translation_request(string $text): \stdClass {
		$result = $this->request(
			'GET',
			'translate?text=' . $text
			. '&source_lang=' . $this->source_lang
			. '&target_lang=' .  $this->target_lang,
		);
		// We need to wait 0.25 secs after every call to  avoid being rate limited.  (DO NOT REMOVE CAN LEAD TO PERMANENT BLOCK)
		usleep(250000);
		return json_decode($result->getBody());
	}
}
