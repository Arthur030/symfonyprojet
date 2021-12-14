<?php

namespace App\Controller;

use App\Entity\Tour;
use App\Form\TourType;
use App\Repository\TourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/tour')]
class TourController extends AbstractController
{
    #[Route('/', name: 'tour')]
    public function index(TourRepository $tourRepository): Response
    {
        $tours = $tourRepository->findAll();
        return $this->render('tour/index.html.twig', [
            'tours' => $tours,
        ]);
    }

    #[Route('/new', name: 'tour_new')]
    public function new(Request $req, EntityManagerInterface $em): Response
    {
        // Deny acces to non logged in users
        $this->denyAccessUnlessGranted('ROLE_USER');
        // Create new Tour
        $tour = new Tour();
        // Create new form from TourType
        $form = $this->createForm(TourType::class, $tour);
        $form->handleRequest($req);
        // Check if submitted & valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Sets current user.id to tour.user.id
            $tour->setUser($this->getUser());
            // Download the picture locally
            $tourPicture = $form->get('img')->getData();
            if ($tourPicture) {
                $pictureName = md5(uniqid(rand())) . "." . $tourPicture->guessExtension();
                $pictureDestination = $this->getParameter('tours_pictures_dir');
                try {
                    $tourPicture->move($pictureDestination, $pictureName);
                } catch (FileException $e) {
                    throw new HttpException(500, 'an error occured during file upload');
                }
                $tour->setimg($pictureName);
            }

            $em->persist($tour);
            $em->flush();

            return $this->redirectToRoute('tour');
        }
        return $this->render('tour/create.html.twig', [
            "tourForm" => $form->createView()
        ]);
    }

    #[Route('/edit/{id}', name: 'tour_update')]
    public function update(Request $req, Tour $tour, EntityManagerInterface $em): Response
    {
        // Deny acces to non logged in users
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(TourType::class, $tour);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($tour);
            $em->flush();

            return $this->redirectToRoute('tour');
        }
        return $this->render('tour/update.html.twig', [
            'tourForm' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'tour_delete')]
    public function delete(Tour $tour, ManagerRegistry $doctrine): Response
    {
        // Deny acces to non logged in users
        $this->denyAccessUnlessGranted('ROLE_USER');

        $em = $doctrine->getManager();
        $user = $this->getUser();
        if ($user->getId() == $tour->getUser()->getId()) {
            $em->remove($tour);
            $em->flush();
        }
        return $this->redirectToRoute('tour');
    }
}
