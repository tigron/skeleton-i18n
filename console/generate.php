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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Cursor;

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
		\Skeleton\Core\Application::get_all();
		$translators = \Skeleton\I18n\Translator::get_all();

		foreach ($translators as $translator) {
			$this->translate_translator($translator, $input, $output);
		}

		return 0;
	}

	/**
	 * Translate translator
	 * 
	 * @param \Skeleton\I18n\Translator $translator
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return void
	 */
	private function translate_translator($translator, InputInterface $input, OutputInterface $output): void {
		ProgressBar::setFormatDefinition('custom', ' [%bar%] %current%/%max% -- %message%');
		$cursor = new Cursor($output);
		$output->writeln('Generating translations for ' . $translator->get_name() . ': ');

		$strings = $translator->get_translator_extractor()->get_strings();

		$translations = [];
		foreach ($strings as $string) {
			$translations[$string] = null;
		}
		ksort($translations);
		$language_interface = \Skeleton\I18n\Config::$language_interface;
		$languages = $language_interface::get_all();

		/**
		 * UI stuff
		 */
		$output->write("\t");
		$output->writeln('');
		$cursor->moveUp();
		$language_x = $cursor->getCurrentPosition()[0];
		$language_y = $cursor->getCurrentPosition()[1];

		foreach ($languages as $language) {
			if (!$language->is_translatable()) {
				continue;
			}

			/**
			 * UI stuff
			 */
			$cursor->moveToPosition($language_x, $language_y-1);
			$output->write($language->name_short. ' ' );
			$language_x = $cursor->getCurrentPosition()[0];
			$cursor->moveToPosition(0, $language_y+1);

			$translator_storage = $translator->get_translator_storage();
			$translator_storage->set_language($language);
			$translator_storage->set_name($translator->get_name());
			$translator_storage->open();
			$existing_translations = $translator_storage->get_translations();

			$progressBar = new ProgressBar($output, count($existing_translations));
			$progressBar->setFormat('custom');
			$progressBar->setMessage('Cleaning unused translations');

			foreach ($existing_translations as $string => $existing_translation) {
				if (!array_key_exists($string, $translations)) {
					$translator_storage->delete_translation($string);
				}
				
				$progressBar->advance();
			}

			$progressBar->finish();
			$progressBar->clear();

			$progressBar = new ProgressBar($output, count($translations));
			$progressBar->setFormat('custom');
			$progressBar->setMessage('Adding new translations');
			
			$contains_new = false;
			foreach ($translations as $string => $translated) {
				if (isset($existing_translations[$string]) === true) {
					// translation already exists
					continue;
				}
				
				$contains_new = true;
				$translator_storage->add_translation($string, '');
				$progressBar->advance();
			}
			
			if ($contains_new === true) {
				$translator_storage->close();
			}
			
			$progressBar->finish();
			$progressBar->clear();
		}
		
		$output->writeln('');
	}
}
