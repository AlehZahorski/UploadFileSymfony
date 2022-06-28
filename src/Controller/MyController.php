<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Service\PhotoVisibilityService;
use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class MyController extends AbstractController
{
    /**
     * @Route ("my/photos", name="app_my_photos")
     */
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        $em = $this->getDoctrine()->getManager();
        $myPhotos = $em->getRepository(Photo::class)->findBy(['user' => $this->getUser()]);
        return $this->render('my/index.html.twig',[
            'myPhotos' => $myPhotos
        ]);
    }

    /**
     *@Route ("my/photos/set_visibility/{id}/{visibility}",name="app_my_photos_set_visibility")
     * @param PhotoVisibilityService $photoVisibilityService
     * @param int $id
     * @param bool $visibility
     */
    public function myPhotoChangeVisibility(PhotoVisibilityService $photoVisibilityService, int $id, bool $visibility): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $messages = [
            '1' => 'publiczne',
            '0' => 'prywatne'
        ];
        if($photoVisibilityService->makeVisible($id, $visibility)) {
            $this->addFlash('success', 'Zdjęcie ustawione jako ' . $messages[$visibility]);
        } else {
            $this->addFlash('error', 'Wystąpił błąd przy ustawieniu zdjęcia jako ' . $messages[$visibility]);
        }

        return $this->redirectToRoute('app_my_photos');
    }


    /**
     * @Route ("my/photos/remove/{id}",name="app_my_photos_remove")
     */
    public function myPhotoRemove(int $id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $myPhoto = $em->getRepository(Photo::class)->find($id);

        if($this->getUser() == $myPhoto->getUser())
        {
            $fileManager = new Filesystem();
            $fileManager->remove('images/hosting/'.$myPhoto->getFilename());
            if($fileManager->exists('images/hosting/'.$myPhoto->getFilename()))
            {
                $this->addFlash('error', 'Nie udało się usunąć zdjęcia');
            } else {
                $em->remove($myPhoto);
                $em->flush();
                $this->addFlash('success', 'Zdjęcie zostało usunięte');
            }
        } else {
            $this->addFlash('error', 'Zdjęcie nie zostało usunięte ponieważ nie jesteś jego włascicielem');
        }

        return $this->redirectToRoute('app_my_photos');
    }
}