<?php

namespace App\Controller;

use App\Entity\Objective;
use App\Form\ObjectiveType;
use App\Repository\ObjectiveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/objective')]
class ObjectiveController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'app_objective_index', methods: ['GET'])]
    public function index(ObjectiveRepository $objectiveRepository): Response
    {
        // If the user is an admin, show all objectives, otherwise show only their own
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $objectives = $objectiveRepository->findAll();
        } else {
            $objectives = $objectiveRepository->findByUser($this->getUser());
        }

        return $this->render('objective/index.html.twig', [
            'objectives' => $objectives,
        ]);
    }

    #[Route('/new', name: 'app_objective_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ObjectiveRepository $objectiveRepository): Response
    {
        $objective = new Objective();
        
        // If not admin, set the current user
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $objective->setUser($this->getUser());
        }
        
        $form = $this->createForm(ObjectiveType::class, $objective);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objectiveRepository->save($objective, true);

            $this->addFlash('success', 'Objective created successfully');
            return $this->redirectToRoute('app_objective_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('objective/new.html.twig', [
            'objective' => $objective,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_objective_show', methods: ['GET'])]
    public function show(Objective $objective): Response
    {
        // Check if user has access to this objective
        $this->denyAccessUnlessGranted('view', $objective);
        
        return $this->render('objective/show.html.twig', [
            'objective' => $objective,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_objective_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Objective $objective, ObjectiveRepository $objectiveRepository): Response
    {
        // Check if user has access to edit this objective
        $this->denyAccessUnlessGranted('edit', $objective);
        
        $form = $this->createForm(ObjectiveType::class, $objective);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objectiveRepository->save($objective, true);

            $this->addFlash('success', 'Objective updated successfully');
            return $this->redirectToRoute('app_objective_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('objective/edit.html.twig', [
            'objective' => $objective,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_objective_delete', methods: ['POST'])]
    public function delete(Request $request, Objective $objective, ObjectiveRepository $objectiveRepository): Response
    {
        // Check if user has access to delete this objective
        $this->denyAccessUnlessGranted('delete', $objective);
        
        if ($this->isCsrfTokenValid('delete'.$objective->getId(), $request->request->get('_token'))) {
            $objectiveRepository->remove($objective, true);
            $this->addFlash('success', 'Objective deleted successfully');
        }

        return $this->redirectToRoute('app_objective_index', [], Response::HTTP_SEE_OTHER);
    }
} 