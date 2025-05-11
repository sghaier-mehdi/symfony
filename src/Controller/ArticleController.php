<?php
// src/Controller/ArticleController.php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/articles')] // Base path for admin article management
// #[IsGranted('ROLE_ADMIN')] // We'll grant access per method for more flexibility
class ArticleController extends AbstractController
{
    // This index shows ALL articles for management purposes
    #[Route('/', name: 'app_admin_article_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')] // Or: #[IsGranted('ROLE_ADMIN')] or is_granted('ROLE_PSYCHIATRIST')
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/admin_index.html.twig', [ // Use a specific admin template
            'articles' => $articleRepository->findBy([], ['createdAt' => 'DESC']), // Order by newest
        ]);
    }

    #[Route('/new', name: 'app_admin_article_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')] // Or: #[IsGranted('ROLE_ADMIN')] or is_granted('ROLE_PSYCHIATRIST')
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // === Automatically set the author to the current logged-in user ===
            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();
            if ($currentUser) { // Should always be true due to IsGranted
                $article->setAuthor($currentUser);
            }
            // ================================================================

            // Timestamps createdAt and updatedAt should be handled by Doctrine
            // if configured with #[ORM\HasLifecycleCallbacks] and PrePersist/PreUpdate methods,
            // OR by database defaults (as per your SQL dump).
            // If using DB defaults, you might not need to set them here.
            // If using HasLifecycleCallbacks, make sure the entity is set up.
            // For simplicity with DB defaults, we can remove explicit setting here if not needed.
            // $article->setCreatedAt(new \DateTimeImmutable()); // If not using DB default or lifecycle

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article created successfully.');

            return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    // Show method for admin (can view published or unpublished)
    #[Route('/{id}', name: 'app_admin_article_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')] // Or: #[IsGranted('ROLE_ADMIN')] or is_granted('ROLE_PSYCHIATRIST')
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [ // Can reuse show template or make admin specific
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_article_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')] // More complex: is_granted('ROLE_ADMIN') or (is_granted('ROLE_PSYCHIATRIST') and article.getAuthor() == user)
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        // Security check: Only allow admin or the original psychiatrist author to edit
        // (if you want Psychiatrists to only edit their own articles)
        if (!$this->isGranted('ROLE_ADMIN')) {
            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();
            if ($this->isGranted('ROLE_PSYCHIATRIST') && $article->getAuthor() !== $currentUser) {
                throw $this->createAccessDeniedException('You are not allowed to edit this article.');
            }
        }


        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // UpdatedAt is handled by DB default or lifecycle callback
            // $article->setUpdatedAt(new \DateTimeImmutable()); // If not using DB default or lifecycle
            $entityManager->flush();

            $this->addFlash('success', 'Article updated successfully.');

            return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_article_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')] // Or: is_granted('ROLE_ADMIN') or (is_granted('ROLE_PSYCHIATRIST') and article.getAuthor() == user)
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        // Security check similar to edit if needed
        if (!$this->isGranted('ROLE_ADMIN')) {
            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();
            if ($this->isGranted('ROLE_PSYCHIATRIST') && $article->getAuthor() !== $currentUser) {
                throw $this->createAccessDeniedException('You are not allowed to delete this article.');
            }
        }

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
            $this->addFlash('success', 'Article deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }


        return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
    }
}