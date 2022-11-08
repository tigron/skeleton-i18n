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
	$translator_storage_po->set_storage_path($root_path . '/po/');	
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

	/**
	 * Optional:
	 * Set another Language interface
	 */
	\Skeleton\I18n\Config::$language_interface = '\Language';

    /**
	 * Optional:
	 * Enable auto fill po file when requesting translation
     * Default to false
	 */
	\Skeleton\I18n\Config::$auto_fill_po = true;


Use it:

Via a twig template rendered by skeleton-template-twig:

	{% trans "To be translated" %}


