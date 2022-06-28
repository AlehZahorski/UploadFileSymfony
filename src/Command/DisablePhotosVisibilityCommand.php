<?php

namespace App\Command;

use App\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisablePhotosVisibilityCommand extends Command
{
    protected static $defaultName = 'app:photo-visible-false';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Ustawia wszystkie zdjęcia jako prywatne dla danego id użytkownika')
            ->addArgument('user', InputArgument::REQUIRED, 'id użytkownika jest wymagane');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->entityManager;
        $photoRepository = $em->getRepository(Photo::class);
        $photoToSetPrivate = $photoRepository->findBy(['is_public' => 1, 'user' => $input->getArgument('user')]);

        foreach ($photoToSetPrivate as $publicPhoto)
        {
            $publicPhoto->setIsPublic(0);
            $em->persist($publicPhoto);
            $em->flush();
        }

        return 0;
    }
}