# skeleton-i18n

## Description

This library enables internationalization and translation features in Skeleton.

## Installation

Installation via composer:

    composer require tigron/skeleton-i18n

Run the migrations to update the database schema:

	skeleton migrate:up

## Howto

Translate a Skeleton App:


	/**
	 * Create a translator object
	 */
	$translator = new \Skeleton\I18n\Translator($application->name);

	/**
	 * Attach a storage
	 */
	$translator_storage_po = new \Skeleton\I18n\Translator\Storage\Po();
	$translator_storage_po->set_configuration([
		'storage_path' => $root_path . '/po/'
	]);
	$translator->set_translator_storage($translator_storage_po);

	/**
	 * Use an extractor to extract translations from templates
	 */
	$translator_extractor_twig = new \Skeleton\I18n\Translator\Extractor\Twig();
	$translator_extractor_twig->set_template_path($application->template_path);
	$translator->set_translator_extractor($translator_extractor_twig);

	[ optional ]
 	/**
	 * Use a translator service to automatically translate untranslated strings
	 */
	$tas_service = new \Skeleton\I18n\Translator\Service\Tas();
	$translator->set_translator_service($tas_service);

	/**
	 * Save the translator
	 */
	$translator->save();

	/**
	 * To translate, get the translation object and ask for a translation
	 */
	$translation = $translator->get_translation( Language::get_by_name_short('nl') );
	echo $translation->translate('This is a test');


Translator\Storage objects can have a default configuration. This configuration
will be used for any newly created Translator\Storage object.

	\Skeleton\I18n\Translator\Storage\Po::set_default_configuration([
		'storage_path' => $root_path . '/po/'
	]);

	/**
	 * Optional:
	 * Set another Language interface
	 */
	\Skeleton\I18n\Config::$language_interface = '\Language';

#### Use it:

Via a twig template rendered by skeleton-template-twig:

	{% trans "To be translated" %}

#### Po files:

Po files can now contain the fuzzy flag: **#, fuzzy** this makes it clear which translations still need work. 


#### Translator services:

A new translator service has been added, this is to make it possible to automatically translate untranslated strings.
Translations made by a translator service will get the fuzzy flag. 

Currently supported translator services: 
- TAS: \Skeleton\I18n\Translator\Service\Tas() => https://github.com/Uncover-F/TAS
- Deepl: \Skeleton\I18n\Translator\Service\Deepl() => https://www.deepl.com/en/pro-api/
