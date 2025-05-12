<?php
// src/Controller/ReclamationController.php
namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository; // For fetching user's reclamations
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted; // For security
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

#[Route('/reclamation')] // Base route for all actions in this controller
#[IsGranted('ROLE_USER')] // Ensure only logged-in users can access
class ReclamationController extends AbstractController
{
    /**
     * Displays a list of reclamations for the current logged-in user.
     */
    #[Route('/mes-reclamations', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            // Should not happen due to IsGranted, but good practice
            return $this->redirectToRoute('app_login'); // Or your login route
        }

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findBy(['user' => $user], ['dateCreation' => 'DESC']),
        ]);
    }

    /**
     * Allows a user to create a new reclamation.
     */
    #[Route('/nouvelle', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, NotifierInterface $notifier): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
             return $this->redirectToRoute('app_login');
        }

        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setUser($user);
            $reclamation->setDateCreation(new \DateTimeImmutable());
            // Sentiment can be set here if you use an API, or based on form if you kept manual input

            $entityManager->persist($reclamation);
            $entityManager->flush();

            // Add a flash message for success
            $this->addFlash('success', 'Votre réclamation a été envoyée avec succès !');

            // Optional: Send a notification to admin (using Symfony Notifier or custom logic)
            // $notifier->send(new Notification('Nouvelle réclamation soumise par ' . $user->getUserIdentifier(), ['chat/admin']));

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    // Potentially add a show method if you want a dedicated page per reclamation for users
    /*
    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        // Ensure the current user owns this reclamation or is admin
        $this->denyAccessUnlessGranted('VIEW', $reclamation); // Requires a Voter or custom logic

        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }
    */
}