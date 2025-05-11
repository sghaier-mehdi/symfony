<?php
// src/Controller/PublicArticleController.php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles')] // Base path for public articles
class PublicArticleController extends AbstractController
{
    #[Route('/', name: 'app_public_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Fetch only published articles, ordered by creation date (newest first)
        $publishedArticles = $articleRepository->findBy(
            ['isPublished' => true],
            ['createdAt' => 'DESC']
        );

        return $this->render('public_article/index.html.twig', [
            'articles' => $publishedArticles,
        ]);
    }

    #[Route('/{id}', name: 'app_public_article_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(ArticleRepository $articleRepository, int $id): Response
    {
        // Find the article by ID, ensuring it's published
        $article = $articleRepository->findOneBy(['id' => $id, 'isPublished' => true]);

        if (!$article) {
            throw $this->createNotFoundException('The article does not exist or is not published.');
        }

        return $this->render('public_article/show.html.twig', [
            'article' => $article,
        ]);
    }
}