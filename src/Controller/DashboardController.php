<?php

// src/Controller/DashboardController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted; // Use attribute for security

// Secure the entire controller: require logged-in user
#[Route('/dashboard')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser(); // Get the logged-in User entity

        // You can add role-specific logic here or in the template
        $dashboardTemplate = 'dashboard/index.html.twig';
        // Example: if ($user->getRole() === 'ADMIN') { $dashboardTemplate = 'dashboard/admin.html.twig'; }

        return $this->render($dashboardTemplate, [
            'user' => $user,
        ]);
    }
}