<?php

namespace App\Command;

use App\Entity\Manga;
use App\Entity\Chapter;
use App\Entity\LanguageCode;
use App\Helper\MangaMangadexApiHelper;
use App\Helper\TwitterHelper;
use App\Repository\MangaRepository;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LanguageCodeRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class RefreshInfosMangaCommand extends Command
{
  protected static $defaultName = 'app:refresh-infos-manga';

  protected $manager;
  protected $mangaRepo;

  public function __construct(EntityManagerInterface $manager, MangaRepository $mangaRepo, ParameterBagInterface $params)
  {
    $this->manager = $manager;
    $this->mangaRepo = $mangaRepo;
    $this->params = $params;
    $this->twitter = new TwitterHelper($params);

    parent::__construct();
  }

  protected function configure()
  {
    $this
      ->setDescription('Met à jour les informations des mangas');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $output->writeln('<comment>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~</comment>');
    $output->writeln('<info>' . (new \DateTime())->format('Y-m-d H:i:s') . '</info>');
    $output->writeln('<comment>=======================================</comment>');
    $output->writeln('<comment>Récupération de l\'ensemble des mangas.</comment>');

    $mangas = $this->mangaRepo->findAll();
    $countMangas = count($mangas);
    $output->writeln('<comment>=======================================</comment>');
    $output->writeln('<comment>Mise à jour de l\'ensemble des informations des mangas.</comment>');
    $output->writeln('<comment>=======================================</comment>');

    $count = 1;
    $start = microtime(true);
    foreach ($mangas as $manga) {
      $output->writeln('<comment> -- ' . $manga->getName() . ' --' . ' (' . $count . '/' . $countMangas . ') </comment>');

      $mangaMangadexApi = new MangaMangadexApiHelper($this->params, $this->manager, $output, null, null, false);
      $mangaMangadexApi->refreshMangaById($manga->getMangaId());

      $count++;
      sleep(2);
    }

    $io->success('La liste des mangas a bien été mise à jour !');
    $io->note("Temps d'exécution : " . round((microtime(true) - $start) / 60, 2) . " minutes");
    return 0;
  }
}
