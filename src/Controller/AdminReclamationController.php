<?php
// src/Controller/AdminReclamationController.php
namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReponseFormType; // Make sure you have created this form type
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository; // Import ReponseRepository
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reclamation')] // Base route for all reclamation admin actions
#[IsGranted('ROLE_ADMIN')]
class AdminReclamationController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Action to list all reclamations
    #[Route('/', name: 'admin_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $reclamations = $reclamationRepository->findBy([], ['dateCreation' => 'DESC']);
        foreach ($reclamations as $idx => $rec) {
            if (!$rec instanceof \App\Entity\Reclamation) {
                error_log("Item at index $idx is not a Reclamation object: " . get_class($rec));
            }
        }
        // dump($reclamations); // Use dump for detailed inspection
        return $this->render('admin_reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    // Action to respond to a reclamation (handles new AND edit of response)
    #[Route('/{id}/respond', name: 'admin_reclamation_respond', methods: ['GET', 'POST'])]
    public function respond(Request $request, Reclamation $reclamation): Response
    {
        $reponse = $reclamation->getReponse() ?? new Reponse(); // Get existing or create new

        if ($reponse->getId() === null) { // If it's a new Reponse
            $reponse->setReclamation($reclamation);
            // DateReponse will be set on submit
        }

        $form = $this->createForm(ReponseFormType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setDateReponse(new \DateTimeImmutable()); // Set/update date on save
            if (!$reclamation->getReponse()) {
                $reclamation->setReponse($reponse); // Link if it was new
            }

            $this->entityManager->persist($reponse);
            // No need to persist $reclamation again if only $reponse changed, unless $reclamation->setReponse was called
            if ($reclamation->getReponse() === $reponse && $reponse->getId() === null) {
                $this->entityManager->persist($reclamation);
            }
            $this->entityManager->flush();

            $this->addFlash('success', $reponse->getId() && $reclamation->getReponse() ? 'Réponse modifiée avec succès!' : 'Réponse ajoutée avec succès!');
            // TODO: Notify user

            return $this->redirectToRoute('admin_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_reclamation/respond.html.twig', [
            'reclamation' => $reclamation,
            'reponse' => $reponse,
            'reponseForm' => $form->createView(),
            'page_title' => $reponse->getId() ? 'Modifier la Réponse' : 'Répondre à la Réclamation',
        ]);
    }

    // Action to show a response (optional, as respond form also shows it for edit)
    // If you want a separate "View Response" button, this route is needed.
    #[Route('/response/{id}/show', name: 'admin_reclamation_show_response', methods: ['GET'])]
    public function showResponse(Reponse $reponse): Response
    {
        // $reponse is automatically fetched by its ID
        return $this->render('admin_reponse/show.html.twig', [ // Use admin_reponse folder for this specific view
            'reponse' => $reponse,
            'reclamation' => $reponse->getReclamation(),
        ]);
    }

    // Action to delete a reclamation
    #[Route('/{id}/delete', name: 'admin_reclamation_delete', methods: ['POST'])]
    public function deleteReclamation(Request $request, Reclamation $reclamation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            // TODO: Notify user before deletion
            $userToNotify = $reclamation->getUser();
            $reclamationTitle = $reclamation->getTitre();
            if ($userToNotify) {
                 error_log("TODO: Admin deleted reclamation titled: " . $reclamationTitle . " for user: " . $userToNotify->getUserIdentifier());
            }

            $this->entityManager->remove($reclamation); // Cascade remove should handle linked Reponse if set up
            $this->entityManager->flush();
            $this.addFlash('success', 'Réclamation supprimée avec succès.');
        } else {
            $this.addFlash('danger', 'Invalid CSRF token.');
        }
        return $this->redirectToRoute('admin_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    // Action to delete a response
    #[Route('/response/{id}/delete', name: 'admin_reclamation_delete_response', methods: ['POST'])]
    public function deleteResponse(Request $request, Reponse $reponse, ReponseRepository $reponseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete_response'.$reponse->getId(), $request->request->get('_token'))) {
            $reclamation = $reponse->getReclamation();
            if ($reclamation) {
                $reclamation->setReponse(null); // Unlink
                $this->entityManager->persist($reclamation);
            }
            $this->entityManager->remove($reponse);
            $this->entityManager->flush();
            $this.addFlash('success', 'Réponse supprimée avec succès.');
            // TODO: Notify user that their response was deleted
        } else {
            $this.addFlash('danger', 'Invalid CSRF token for response deletion.');
        }
        return $this->redirectToRoute('admin_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}