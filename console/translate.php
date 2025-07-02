<?php
/**
 * i18n:translate command for Skeleton Console
 *
 * @author Roan Buysse <roan@tigron.be>
 */

namespace Skeleton\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Cursor;

class I18n_Translate extends \Skeleton\Console\Command {

	/**
	 * Configure the Create command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('i18n:translate');
		$this->setDescription('Translate the unstransalated strings from the po files with a specific service');
	}

	/**
	 * Execute the Command
	 *
	 * @access protected
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		\Skeleton\Core\Application::get_all();
		$translators = \Skeleton\I18n\Translator::get_all();

		foreach ($translators as $key => $translator) {
			$this->translate_service($translator, $input, $output);
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
	private function translate_service($translator, InputInterface $input, OutputInterface $output): void {
		ProgressBar::setFormatDefinition('custom', ' [%bar%] %current%/%max% -- %message%');
		$cursor = new Cursor($output);

		$translations = [];
		ksort($translations);

		$language_interface = \Skeleton\I18n\Config::$language_interface;
		$languages = $language_interface::get_all();

		if (empty($translator->get_translator_service()) === true) {
			$output->writeln('No translator service set for ' . $translator->get_name() . ': ');
			return;
		}
		$output->writeln('Translating untranslated strings for ' . $translator->get_name() . ': ');

		/**
		 * UI stuff
		 */
		$output->write("\t");
		$output->writeln('');
		$cursor->moveUp();
		$language_x = $cursor->getCurrentPosition()[0];
		$language_y = $cursor->getCurrentPosition()[1];


		$translator_service = $translator->get_translator_service();
		$translator_storage = $translator->get_translator_storage();

		foreach ($languages as $language) {
			if ($language->is_translatable() == false) {
				continue;
			}
			$translator_service->set_target_language($language);

			/**
			 * UI stuff
			 */
			$cursor->moveToPosition($language_x, $language_y-1);
			$output->write($language->name_short. ' ' );
			$language_x = $cursor->getCurrentPosition()[0];
			$cursor->moveToPosition(0, $language_y+1);

			$translator_storage->set_language($language);
			$translator_storage->set_name($translator->get_name());
			$translator_storage->open();
			$trans_entries = $translator_storage->get_translations();
			$progressBar = new ProgressBar($output, count($trans_entries));
			$progressBar->setFormat('custom');
			$progressBar->setMessage('Updating translations');

			$modified = false;
			foreach ($trans_entries as $trans_entry) {
				// We only want to use the service if the string has not been translated.
				if ($trans_entry->is_translated() === true) {
					$progressBar->advance();
					continue;
				}

				$trans_entry = $translator_service->translate($trans_entry);
				$translator_storage->update_translation_entry($trans_entry);
				$modified = true;

				$progressBar->advance();
			}

			$progressBar->finish();
			$progressBar->clear();

			if ($modified === true) {
				$translator_storage->close();
			}

			$progressBar->finish();
			$progressBar->clear();
		}

		$output->writeln('');
	}
}
