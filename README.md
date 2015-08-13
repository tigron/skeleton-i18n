# skeleton-i18n

## Description

This library enables internationalization and translation features in Skeleton.


## Installation

Installation via composer:

    composer require tigron/skeleton-i18n

Create a new table in your database:

	CREATE TABLE IF NOT EXISTS `language` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `name_local` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `name_short` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	  `name_ogone` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
	  PRIMARY KEY (`id`),
	  FULLTEXT KEY `name_short` (`name_short`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


## Howto

TODO
