<?php

namespace App\Command;

use App\Entity\Manga;
use App\Entity\Chapter;
use App\Entity\LanguageCode;
use App\Repository\MangaRepository;
use Abraham\TwitterOAuth\TwitterOAuth;
use Doctrine\ORM\EntityManagerInterface;
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
        $output->writeln('<comment>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~</comment>')
            ->writeln('<info>' . (new \DateTime())->format('Y-m-d H:i:s') . '</info>')
            ->writeln('<comment>=======================================</comment>')
            ->writeln('<comment>Récupération de l\'ensemble des mangas.</comment>');

        $mangas = $this->mangaRepo->findAll();

        $output->writeln('<comment>=======================================</comment>')
            ->writeln('<comment>Mise à jour de l\'ensemble des informations des mangas.</comment>')
            ->writeln('<comment>=======================================</comment>');

        foreach ($mangas as $manga) {
            $output->writeln('<comment> -- ' . $manga->getName() . ' -- </comment>');
            $this->refreshInfos($manga->getMangaId(), $this->manager, $output);
            $output->writeln('<info> OK </info>');
        }

        $io->success('La liste des mangas a bien été mise à jour !');

        return 0;
    }

    private function refreshInfos(string $mangaId, EntityManagerInterface $manager, OutputInterface $output)
    {
        $mangaRepo = $manager->getRepository(Manga::class);
        $langCodeRepo = $manager->getRepository(LanguageCode::class);
        $chapterRepo = $manager->getRepository(Chapter::class);

        $mangadexURL =  $this->params->get('mangadex_url');

        $client = HttpClient::create(['http_version' => '2.0']);
        $response = $client->request('GET', $mangadexURL . '/api/manga/' . $mangaId);

        if ($response->getStatusCode() != 200) {
            $output->writeln("<error>API for can't be reach for this manga</error>");
        }

        $data = json_decode($response->getContent());

        $manga = $data->manga;
        $chapters = $data->chapter;

        $mangaDB = $mangaRepo->findOneBy(array(
            'mangaId' => $mangaId,
        ));

        $urlImage = $mangadexURL . strtok($manga->cover_url, "?");
        $info = pathinfo($urlImage);
        $image = $info['basename'];

        if (!$mangaDB) {
            $mangaDB = new Manga();
            $mangaDB->setName(html_entity_decode($manga->title, ENT_QUOTES, 'UTF-8'));

            $imageFile = file_get_contents($urlImage);
            $file = $this->params->get('kernel.project_dir') . "/public/uploads/mangas/" . $info['basename'];
            file_put_contents($file, $imageFile);

            $mangaDB->setImage($image)
                ->setMangaId($mangaId);

            $manager->persist($mangaDB);
            $manager->flush();
        } else {
            if ($mangaDB->getMangaId() != $mangaId) {
                $mangaDB->setMangaId($mangaId);
            }

            $manager->persist($mangaDB);
            $manager->flush();
        }

        foreach ($chapters as $chapter_id => $values) {
            if ($values->chapter) {
                $langCode = $values->lang_code;

                $langCodeDB = $langCodeRepo->findOneBy(array(
                    'langCode' => $langCode
                ));

                if ($langCodeDB) {
                    $number = $values->chapter;
                    $timestamp = $values->timestamp;

                    $chapterDB = $chapterRepo->findOneBy(array(
                        'langCode' => $langCodeDB,
                        'manga' => $mangaDB,
                        'number' => $number
                    ));

                    if ($chapterDB) {
                        if ($chapterDB->getDate()->getTimestamp() < $timestamp) {
                            $chapterDB->setChapterId($chapter_id)
                                ->setDate(new \DateTime(date('Y-m-d H:i:s', $timestamp)));

                            $manager->persist($chapterDB);
                            $manager->flush();
                        }
                    } else {
                        $chapter = new Chapter();
                        $chapter->setLangCode($langCodeDB)
                            ->setManga($mangaDB)
                            ->setChapterId($chapter_id)
                            ->setNumber($number)
                            ->setDate(new \DateTime(date('Y-m-d H:i:s', $timestamp)));

                        $manager->persist($chapter);
                        $manager->flush();

                        $output->writeln($mangaDB->getName() . ' - Langue : ' . $langCodeDB->getLibelle() . ' - Chapitre n°' . $chapter->getNumber() . ' ajouté !');

                        $string = $mangaDB->getName() . ' (' . $langCodeDB->getLibelle() . ') - Chapitre n°' . $chapter->getNumber() . ' sortie !' . PHP_EOL;
                        $string .= 'Disponible ici : ' . $mangadexURL . '/chapter/' . $chapter_id;
                        $this->postTweetMangaUpdate($string, array($this->params->get('kernel.project_dir') . "/public/uploads/mangas/" . $image));
                    }
                }
            }
        }
    }


    private function postTweetMangaUpdate($str = '', $mediaArray = null)
    {
        $consumerKey = $this->params->get('consumer_key');
        $consumerSecret = $this->params->get('consumer_secret');
        $oauthToken = $this->params->get('oauth_token');
        $oauthTokenSecret = $this->params->get('oauth_token_secret');

        if (!empty($consumerKey) && !empty($consumerSecret) && !empty($consumerKey) && !empty($oauthTokenSecret)) {
            $connection =  new TwitterOAuth($consumerKey, $consumerSecret, $oauthToken, $oauthTokenSecret);

            if (is_array($mediaArray)) {

                $mediaIDS = array();

                foreach ($mediaArray as $key => $media_path) {
                    if (!is_readable($media_path) || file_get_contents($media_path) === false) {
                        // TODO logger
                    } else {
                        $mediaOBJ = $connection->upload('media/upload', ['media' => $media_path]);
                        array_push($mediaIDS, $mediaOBJ->media_id_string);
                    }
                }

                $mediaIDstr = implode(',', $mediaIDS);
            }

            $arrayCfg['status'] = $str;
            $arrayCfg['media_ids'] = $mediaIDstr;

            $statuses = $connection->post("statuses/update", $arrayCfg);

            return $statuses;
        }

        return 0;
    }
}
