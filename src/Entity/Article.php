<?php
// src/Entity/Article.php - Converted to #[ORM\...] Attributes

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types; // Keep if you use Types::... in your entity or forms
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'articles')]
// Add indexes if you want Doctrine to manage them (optional, DB dump already has them)
// #[ORM\Index(columns: ['author_id'], name: 'idx_article_author')]
// #[ORM\Index(columns: ['category_id'], name: 'idx_article_category')]
// #[ORM\Index(columns: ['is_published', 'created_at'], name: 'idx_article_published_created')]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    // Relationship ManyToOne
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'authoredArticles')]
    #[ORM\JoinColumn(nullable: false)] // JoinColumn attribute
    private ?User $author = null;

    // Relationship ManyToOne
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: true)] // JoinColumn attribute
    private ?Category $category = null;

    #[ORM\Column(type: 'boolean')] // Tinyint(1) maps to boolean
    private bool $isPublished = false;

    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, columnDefinition: "TIMESTAMP DEFAULT CURRENT_TIMESTAMP")] // Use columnDefinition for complex defaults
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, columnDefinition: "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP")] // Use columnDefinition for complex defaults
    private ?\DateTimeInterface $updatedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}