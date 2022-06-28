<?php

namespace App\Controller;

use App\Entity\Photo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LatestPhotosController extends AbstractController
{
    /**
     * @Route ("/latest", name="app_latest_photos")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $latestPhotosPublic = $em->getRepository(Photo::class)->findBy(['is_public' => true]);

        return $this->render('latest_photos/index.html.twig',[
            'latestPhotosPublic' => $latestPhotosPublic
        ]);
    }
}