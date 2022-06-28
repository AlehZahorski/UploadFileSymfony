<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Form\UploadPhotoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(UploadPhotoType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            if($this->getUser())
            {
                /** @var UploadedFile $pictureFilename */
                $pictureFilename = $form->get('filename')->getData();
                if($pictureFilename)
                {
                    try {
                        $originalFilename = pathinfo($pictureFilename->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename . '-' .uniqid() . '.' .$pictureFilename->guessClientExtension();
                        $pictureFilename->move('images/hosting', $newFilename);

                        $entityPhotos = new Photo();
                        $entityPhotos->setFilename($newFilename);
                        $entityPhotos->setIsPublic($form->get('is_public')->getData());
                        $entityPhotos->setUploadedAt(new \DateTimeImmutable());
                        $entityPhotos->setUser($this->getUser());

                        $em->persist($entityPhotos);
                        $em->flush();

                        $this->addFlash('success','Dodane zdjęcie');
                    } catch (\Exception $error) {
                        $this->addFlash('error', 'Nie udało się wgrać zdjęcie na serwer');
                    }
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }


}
