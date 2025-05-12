<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    // --- ADDED TYPE FOR DATETIME ---
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: "created_at")]
    private ?\DateTimeImmutable $dateReponse = null;

    // --- CORRECTED INVERSEDBY TARGET ---
    #[ORM\OneToOne(inversedBy: 'reponse', targetEntity: Reclamation::class, cascade: ['persist', 'remove'])] // Corrected inversedBy to 'reponse'
    #[ORM\JoinColumn(nullable: false)]
    private ?Reclamation $reclamation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDateReponse(): ?\DateTimeImmutable
    {
        return $this->dateReponse;
    }

    public function setDateReponse(\DateTimeImmutable $dateReponse): static
    {
        $this->dateReponse = $dateReponse;

        return $this;
    }

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(Reclamation $reclamation): static
    {
        $this->reclamation = $reclamation;

        return $this;
    }
}
