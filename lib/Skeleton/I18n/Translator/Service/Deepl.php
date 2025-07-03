<?php
/**
 * Service Deepl
 *
 * @author Roan Buysse <roan@tigron.be>
 */
namespace Skeleton\I18n\Translator\Service;

class Deepl extends \Skeleton\I18n\Translator\Service {

	/**
	 * Endpoint
	 *
	 * @var $endpoint
	 */
	private $endpoint = 'https://api.deepl.com';

	/**
	 * Endpoint
	 *
	 * @var $endpoint
	 */
	private $api_key = '';

	/**
	 * Api Version
	 *
	 * @var $api_version
	 */
	private $api_version = 'v2';

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
	 * Api client
	 *
	 * @var \GuzzleHttp\Client $client
	 */
	private $client;

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
	}

	/**
	 * Initialise the api client
	 *
	 * @return void
	 */
	private function init_client(): void {
		if (isset($this->client) === false) {
			$config = [
				'base_uri' => $this->endpoint,
				'headers' => [
					'Content-Type' => 'application/json; charset=UTF8',
					'Authorization' => 'DeepL-Auth-Key ' . $this->api_key
				],
			];
			$this->client = new \GuzzleHttp\Client($config);
		}
	}

	/**
	 * Returns a translation entry object with the destination (translation)
	 *
	 * @param \Skeleton\I18n\Translation\Entry $entry
	 * @return \Skeleton\I18n\Translation\Entry
	 */
	public function translate(\Skeleton\I18n\Translation\Entry $entry): \Skeleton\I18n\Translation\Entry {
		$this->init_client();

		if (empty($this->api_key) === true) {
			throw new \Exception("An Api key needs to be set to use the DeepL translator service");
		}

		$result = $this->translation_request($entry->source);
		$translation = array_shift($result->translations);
		$entry->set($translation->text, true);
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
	 * Set the endpoint for a service
	 *
	 * @param \Language $language
	 * @return void
	 */
	public function set_endpoint(string $endpoint): void {
		$this->endpoint = $endpoint;
	}

	/**
	 * Set the api_version for a service
	 *
	 * @param \Language $version
	 * @return void
	 */
	public function set_api_version(string $version): void {
		$this->api_version = $version;
	}


	/**
	 * Set the api_key for a service
	 *
	 * @param \String $api_key
	 * @return void
	 */
	public function set_api_key(string $api_key): void {
		$this->api_key = $api_key;
	}

	/**
	 * Translation request to the api
	 *
	 * @param string $text
	 * @return \stdClass
	 */
	private function translation_request(string $text): \stdClass {
		$options['form_params'] = [
			'text' => $text,
			'source_lang' => $this->source_lang,
			'target_lang' => $this->target_lang,
		];

		$result = $this->client->request('POST',  $this->api_version . '/translate', $options);
		return json_decode($result->getBody());
	}
}
