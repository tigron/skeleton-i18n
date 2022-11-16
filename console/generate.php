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
		\Skeleton\Core\Application::get_all();
		$translators = \Skeleton\I18n\Translator::get_all();

		foreach ($translators as $translator) {
			$output->write($translator->get_name() . ': ');
			$log = $translator->generate_translations();
			$output->writeln($log);
		}

		return 0;
	}
}
