<?php
// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType; // This will be the form for Admin User management
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Import for password hashing

#[Route('/admin/users')] // *** Set the base path for these routes ***
#[IsGranted('ROLE_ADMIN')] // *** Restrict the entire controller to ADMIN role ***
class UserController extends AbstractController
{
    #[Route('/', name: 'app_users_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Fetch all users from the repository
        $users = $userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        // When creating a new user, require the password field in the form
        $form = $this->createForm(UserType::class, $user, ['require_password' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             // Get the plain password from the form (it's mapped=false)
             $plainPassword = $form->get('plainPassword')->getData();

             // === Hash the password before saving ===
             if ($plainPassword) { // Only hash if a password was entered
                  $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                  $user->setPassword($hashedPassword);
             }
             // ======================================

             // The role is handled by the form field now

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created successfully.');

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_users_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // When editing a user, the password field is optional
        $form = $this->createForm(UserType::class, $user, ['require_password' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             // Get the plain password from the form if it was entered during edit
             $plainPassword = $form->get('plainPassword')->getData();

             // === Hash the new password if it was entered ===
             if ($plainPassword) { // Only hash if a *new* password was provided
                  $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                  $user->setPassword($hashedPassword);
             }
             // ==============================================

             // The role is handled by the form field now

             $entityManager->flush(); // Use flush, not persist+flush for editing

             $this->addFlash('success', 'User updated successfully.');

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Optional: Prevent deleting the currently logged-in user account you are using
        if ($user === $this->getUser()) {
             $this->addFlash('error', 'You cannot delete the currently logged-in user.');
             return $this->redirectToRoute('app_users_index'); // Redirect back
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
             $this->addFlash('success', 'User deleted successfully.');
        } else {
             $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }
}