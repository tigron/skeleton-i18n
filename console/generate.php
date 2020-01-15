<?php
/**
 * i18n:generate command for Skeleton Console
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;

class I18n_Generate extends \Skeleton\Console\Command {

	protected $twig_extractor = null;

	/**
	 * Configure the Create command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('i18n:generate');
		$this->setDescription('Generate po files based on application templates');
	}

	/**
	 * Execute the Command
	 *
	 * @access protected
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {

		// Fetch paths for Applications
		$applications = \Skeleton\Core\Application::get_all();

		// If the paths array hasn't been defined yet, make sure it exists
		if (!isset($paths) or !is_array($paths)) {
			$paths = [];
		}

		foreach ($applications as $application) {
			$paths[$application->name] = $application->path;
		}

		// Fetch additional paths to translate
		foreach (\Skeleton\I18n\Config::$additional_template_paths as $name => $path) {
			$paths[$name] = $path;
		}

		// Translate all the applications
		foreach ($paths as $application => $directory) {
			$log = $this->translate_application($application, $directory);
			$output->writeln($log);
		}

		$packages = \Skeleton\Core\Package::get_all();

		foreach ($packages as $package) {
			$log = $this->translate_skeleton_package($package);
			$output->writeln($log);
		}

		return 0;
	}

	/**
	 * Translate a skeleton package
	 *
	 * @param string $application Name of the application
	 * @param string $directory Application path
	 */
	private function translate_skeleton_package(\Skeleton\Core\Skeleton $package) {
		$log = '';
		$log .= 'translating ' . $package->name . ' (' . $package->template_path . ')' . "\n";

		// Fetch the templates in this directory
		if (!file_exists($package->template_path)) {
			return;
		}
		$templates = $this->get_templates($package->template_path);
		$strings = [];

		// Parse all the files we found
		foreach ($templates as $template) {
			$strings = array_merge($strings, $this->get_strings($template, $package->template_path));
		}

		// Translate the strings
		$language_interface = \Skeleton\I18n\Config::$language_interface;
		if (!class_exists($language_interface)) {
			throw new \Exception('The language interface does not exists: ' . $language_interface);
		}

		$languages = $language_interface::get_all();

		foreach ($languages as $language) {
			// Don't create a .po file if it is our base_language
			if ($language->name_short == \Skeleton\I18n\Config::$base_language) {
				continue;
			}

			$log .=  ' ' . $language->name_short;

			// If we have a translation in the package, load it
			$package_translated = [];
			if (file_exists($package->path . '/po/' . $language->name_short . '.po')) {
				$package_translated = \Skeleton\I18n\Util::load($package->path . '/po/' . $language->name_short . '.po');
			}

			// If there is a local translation file, load it
			$local_translated = [];
			if (file_exists(\Skeleton\I18n\Config::$po_directory . '/' . $language->name_short . '/package/' . $package->name . '.po')) {
				$local_translated = \Skeleton\I18n\Util::load(\Skeleton\I18n\Config::$po_directory . '/' . $language->name_short . '/package/' . $package->name . '.po');
			}

			// Create a new array with the merged translations
			$new_po = [];
			foreach ($strings as $string) {
				if (isset($local_translated[$string]) and $local_translated[$string] != '') {
					$new_po[$string] = $local_translated[$string];
				} elseif (isset($package_translated[$string]) and $package_translated[$string] != '') {
					$new_po[$string] = $package_translated[$string];
				} else {
					$new_po[$string] = '';
				}
			}

			$result1 = array_diff_key($new_po, $local_translated);
			$result2 = array_diff_key($local_translated, $new_po);
			if (count($result1) == 0 and count($result2) == 0) {
				// No new po file will be created, there are no changes
				continue;
			}

			// Stop doing what we are doing if there are no strings anyway
			if (count($new_po) == 0) {
				continue;
			}

			// And save!
			\Skeleton\I18n\Util::save(\Skeleton\I18n\Config::$po_directory . '/' . $language->name_short . '/package/' . $package->name . '.po', $package->name, $language, $new_po);
		}

		$log .= "\n";
		return $log;
	}

	/**
	 * Translate an application
	 *
	 * @param string $application Name of the application
	 * @param string $directory Application path
	 */
	private function translate_application($application, $directory) {
		$log = '';
		$log .= 'translating ' . $application . ' (' . $directory . ')' . "\n";

		// Fetch the templates in this directory
		$templates = $this->get_templates($directory);
		$strings = [];
		// Parse all the files we found
		foreach ($templates as $template) {
			$log .= $template . "\n";
			$strings = array_merge($strings, $this->get_strings($template, $directory));
		}

		// Translate the strings
		$language_interface = \Skeleton\I18n\Config::$language_interface;
		if (!class_exists($language_interface)) {
			throw new \Exception('The language interface does not exists: ' . $language_interface);
		}
		$languages = $language_interface::get_all();

		foreach ($languages as $language) {
			// Don't create a .po file if it is our base_language
			if ($language->name_short == \Skeleton\I18n\Config::$base_language) {
				continue;
			}

			$log .=  ' ' . $language->name_short;

			// If we already have a (partially) translated file, merge
			if (file_exists(\Skeleton\I18n\Config::$po_directory . '/' . $language->name_short . '/' . $application . '.po')) {
				$translated = \Skeleton\I18n\Util::load(\Skeleton\I18n\Config::$po_directory . '/' . $language->name_short . '/' . $application . '.po');
				$old_translated = \Skeleton\I18n\Util::load(\Skeleton\I18n\Config::$po_directory . '/' . $language->name_short . '.po');
				$translated = array_merge($translated, $old_translated);
			} else {
				$translated = [];
			}

			// Create a new array with the merged translations
			$new_po = [];
			foreach ($strings as $string) {
				if (isset($translated[$string]) and $translated[$string] != '') {
					$new_po[$string] = $translated[$string];
				} else {
					$new_po[$string] = '';
				}
			}

			$result1 = array_diff_key($new_po, $translated);
			$result2 = array_diff_key($translated, $new_po);
			if (count($result1) == 0 and count($result2) == 0) {
				// No new po file will be created, there are no changes
				continue;
			}

			// Stop doing what we are doing if there are no strings anyway
			if (count($new_po) == 0) {
				//continue;
			}

			// And save!
			\Skeleton\I18n\Util::save(\Skeleton\I18n\Config::$po_directory . '/' . $language->name_short . '/' . $application . '.po', $application, $language, $new_po);
		}

		$log .= "\n";
		return $log;
	}

	/**
	 * Parse all translatable stings out of a file
	 *
	 * @param string $file The full path of the file to parse
	 */
	private function get_strings($file, $directory) {
		$parts = explode('.', strrev($file), 2);
		$filename = strrev($parts[1]);
		$extension = strrev($parts[0]);

		switch ($extension) {
			case 'twig':
				$strings = $this->get_twig_strings($file, $directory);
				break;
			case 'tpl':
				$strings = $this->get_smarty_strings($file, $directory);
				break;
			default: throw new \Exception('Unknown template type');
		}

		return $strings;
	}

	private function get_twig_strings($file, $directory) {
		if (!isset($this->twig_extractor[$directory])) {
			$loader = new \Twig\Loader\FilesystemLoader($directory);

			// force auto-reload to always have the latest version of the template
			$twig = new \Twig\Environment($loader, [
				'cache' => \Skeleton\Template\Twig\Config::$cache_directory,
				'auto_reload' => true
			]);

			$twig->addExtension(new \Twig\Extension\StringLoaderExtension());
			$twig->addExtension(new \Skeleton\Template\Twig\Extension\Common());
			$twig->addExtension(new \Skeleton\I18n\Template\Twig\Extension\Tigron());
			$twig->addExtension(new \Twig\Extra\Markdown\MarkdownExtension());
			$twig->addExtension(new \Twig\Extra\String\StringExtension());

			$extensions = \Skeleton\Template\Twig\Config::get_extensions();
			foreach ($extensions as $extension) {
				$twig->addExtension(new $extension());
			}

			$this->twig_extractor[$directory] = new \Skeleton\I18n\Extractor\Twig($twig);
		}

		return $this->twig_extractor[$directory]->extract($file);
	}

	private function get_smarty_strings($file, $directory) {
		$content = file_get_contents($directory . '/' . $file);
		preg_match_all("/\{t\}(.*?)\{\/t\}/", $content, $matches);
		return $matches[1];
	}

	/**
	 * Unescape strings in an array
	 *
	 * @param array $strings
	 * @param string $escape
	 */
	private function unescape_strings($strings, $escape) {
		if (strlen($escape) <> 1) {
			throw new Exception('Escape parameter can only be one character');
		}

		$escaped_strings = [];
		foreach ($strings as $string) {
			$escaped_strings[] = (string) str_replace('\\' . $escape, $escape, $string);
		}

		return $escaped_strings;
	}

	/**
	 * Find all template files in a given directory
	 *
	 * @param string $directory Directory to search for templates
	 */
	private function get_templates($directory) {
		$templates = [];
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory), \RecursiveIteratorIterator::LEAVES_ONLY) as $file)
		{
		    if ($file->isFile() === false) {
		        continue;
		    }

			if ($file->getExtension() != 'twig' && $file->getExtension() != 'tpl') {
				continue;
			}

		    $resource = str_replace($directory, '', $file);
		    $templates[] = $resource;
		}

		return $templates;
	}
}
