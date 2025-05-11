<?php
// src/Controller/ConsultationController.php

namespace App\Controller;

use App\Entity\Consultation;
use App\Form\ConsultationCreateType;
use App\Form\ConsultationManageType;
use App\Repository\ConsultationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;


#[Route('/consultations')]
#[IsGranted('IS_AUTHENTICATED_FULLY')] // Base restriction for most actions
class ConsultationController extends AbstractController
{
     private Security $security;
     private EntityManagerInterface $entityManager;

     public function __construct(Security $security, EntityManagerInterface $entityManager)
     {
         $this->security = $security;
         $this->entityManager = $entityManager;
     }

    /**
     * Lists consultations for the current user based on their role.
     */
    #[Route('/', name: 'app_consultation_index', methods: ['GET'])]
    // #[IsGranted('IS_AUTHENTICATED_FULLY')] // Base restriction already covers this
    public function index(ConsultationRepository $consultationRepository): Response
    {
         /** @var ?\App\Entity\User $user */
        $user = $this->security->getUser();

        $consultations = [];

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $consultations = $consultationRepository->findBy([], ['consultationTime' => 'ASC']);
        } elseif ($this->security->isGranted('ROLE_PSYCHIATRIST')) {
            $consultations = $consultationRepository->findBy(['psychiatrist' => $user], ['consultationTime' => 'ASC']);
        } elseif ($this->security->isGranted('ROLE_PATIENT')) {
            $consultations = $consultationRepository->findBy(['patient' => $user], ['consultationTime' => 'ASC']);
        }

        return $this->render('consultation/index.html.twig', [
            'consultations' => $consultations,
            'user_role' => $user ? $user->getRole() : null,
        ]);
    }

    /**
     * Displays a form for Psychiatrist/Admin to create a new consultation.
     * Handles form submission and saving.
     */
    #[Route('/create', name: 'app_consultation_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PSYCHIATRIST') ] // Only Psych/Admin can create
    public function create(Request $request): Response
    {
         /** @var ?\App\Entity\User $currentUser */
        $currentUser = $this->security->getUser();

        $consultation = new Consultation();

        $form = $this->createForm(ConsultationCreateType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $consultation->setStatus('SCHEDULED');

            if ($this->security->isGranted('ROLE_PSYCHIATRIST')) {
                 $consultation->setPsychiatrist($currentUser);
            }
            // Admin selected psychiatrist in the form

            // *** Availability Conflict Check (Optional, Complex) Goes Here ***
            // Needs custom repository method

            try {
                 $this->entityManager->persist($consultation);
                 $this->entityManager->flush();

                 $this->addFlash('success', 'Consultation created successfully.');
                 return $this->redirectToRoute('app_consultation_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                 $this->addFlash('error', 'An error occurred while creating the consultation.');
                 // Log error $this->logger->error(...)
                 return $this->render('consultation/create.html.twig', [
                     'consultation' => $consultation,
                     'form' => $form->createView(),
                 ]);
            }
        }

        return $this->render('consultation/create.html.twig', [
            'consultation' => $consultation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays details for a specific consultation.
     * Access is restricted to Admin, the Patient involved, or the Psychiatrist involved.
     */
    #[Route('/{id}', name: 'app_consultation_show', methods: ['GET'])]
    // *** CORRECTED IsGranted attribute syntax ***
    // Check if user has ROLE_ADMIN OR (consultation's patient is the current user) OR (consultation's psychiatrist is the current user)
    #[IsGranted('ROLE_ADMIN or consultation.getPatient() == user or consultation.getPsychiatrist() == user')]
    public function show(Consultation $consultation): Response
    {
         /** @var ?\App\Entity\User $user */
        $user = $this->security->getUser();

        // Access control is handled by the #[IsGranted] attribute above

        return $this->render('consultation/show.html.twig', [
            'consultation' => $consultation,
            'user_role' => $user ? $user->getRole() : null,
        ]);
    }

    /**
     * Displays a form for Psychiatrist/Admin to edit status and notes of a consultation.
     * Handles form submission and saving changes.
     * Accessible to Admin role or the assigned Psychiatrist.
     */
    #[Route('/{id}/edit', name: 'app_consultation_edit', methods: ['GET', 'POST'])]
    // *** CORRECTED IsGranted attribute syntax ***
    // Check if user has ROLE_ADMIN OR (consultation's psychiatrist is the current user)
    #[IsGranted('ROLE_ADMIN or consultation.getPsychiatrist() == user')]
    public function edit(Request $request, Consultation $consultation): Response
    {
        // Use the ConsultationManageType form for status/notes
        $form = $this->createForm(ConsultationManageType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Status and Notes are automatically updated by the form

            $this->entityManager->flush();

            $this->addFlash('success', 'Consultation updated successfully.');

            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultation/edit.html.twig', [
            'consultation' => $consultation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handles deletion of a consultation via a POST request.
     * Restricted to Admin role.
     */
    #[Route('/{id}', name: 'app_consultation_delete', methods: ['POST'])]
    // *** CORRECTED IsGranted attribute syntax (only Admin) ***
    #[IsGranted('ROLE_ADMIN')] // Only Admin can delete consultations
    public function delete(Request $request, Consultation $consultation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$consultation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($consultation);
            $this->entityManager->flush();

            $this->addFlash('success', 'Consultation deleted successfully.');

        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_consultation_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * Displays the calendar view for the logged-in Patient.
     * Accessible only to Patient role.
     */
    #[Route('/my-calendar', name: 'app_consultation_patient_calendar', methods: ['GET'])]
    #[IsGranted('ROLE_PATIENT')] // Only Patients can see their calendar
    public function patientCalendar(ConsultationRepository $consultationRepository): Response
    {
         /** @var ?\App\Entity\User $patient */
        $patient = $this->security->getUser(); // Get the logged-in patient (guaranteed to be PATIENT by IsGranted)

        // Fetch consultations specifically for this patient
        $patientConsultations = $consultationRepository->findBy(['patient' => $patient], ['consultationTime' => 'ASC']);

        // Prepare data for the JavaScript calendar (FullCalendar format)
        $events = [];
        foreach ($patientConsultations as $consultation) {
             $startTime = $consultation->getConsultationTime();
             $endTime = $startTime;

             if ($startTime && $consultation->getDurationMinutes()) {
                 $endTime = (clone $startTime)->modify('+' . $consultation->getDurationMinutes() . ' minutes');
             }

             $events[] = [
                 'id' => $consultation->getId(),
                 'title' => 'Consultation with Dr. ' . $consultation->getPsychiatrist()?->getFullName(),
                 'start' => $startTime?->format(\DateTimeInterface::ISO8601),
                 'end' => $endTime?->format(\DateTimeInterface::ISO8601),
                 'purpose' => $consultation->getPurpose(),
                 'status' => $consultation->getStatus(),
                 'url' => $this->generateUrl('app_consultation_show', ['id' => $consultation->getId()]),
                 'backgroundColor' => ($consultation->getStatus() === 'CANCELLED') ? '#dc3545' : '#28a745',
                 'borderColor' => 'transparent',
                 'textColor' => '#fff',
             ];
         }

        return $this->render('consultation/patient_calendar.html.twig', [
            'patient' => $patient,
            'events_json' => json_encode($events),
        ]);
    }
}