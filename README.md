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

Use it:

Via a twig template rendered by skeleton-template-twig:

	{% trans "To be translated" %}

