<?php

declare(strict_types=1);

namespace App\News\Command;

use App\News\Services\NewsDownloader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
	name: 'app:news:download',
	description: 'Загрузка новостей за последние сутки',
	hidden: false,
)]
class NewsDownloadCommand extends Command
{
	public function __construct(
		private NewsDownloader $NewsDownloader,
	) {
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$output->writeln([
			'Запущена загрузка новостей за последние сутки',
		]);

		$this->NewsDownloader->handle();

		$io->success('Новости за сутки успешно загружены');

		return Command::SUCCESS;
	}
}
