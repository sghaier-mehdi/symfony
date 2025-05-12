<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, name: "date_creation")]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $sentiment = null;

    #[ORM\Column(length: 100, name: "typeReclamation")]
    private ?string $typeReclamation = null;

    #[ORM\Column]
    private ?bool $urgent = null;

    #[ORM\Column]
    private ?bool $followUp = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // --- CORRECTED PROPERTY NAME AND MAPPEDBY TARGET ---
    #[ORM\OneToOne(mappedBy: 'reclamation', targetEntity: Reponse::class, cascade: ['persist', 'remove'])]
    private ?Reponse $reponse = null; // Changed from $Reclamation to $reponse

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
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

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getSentiment(): ?string
    {
        return $this->sentiment;
    }

    public function setSentiment(?string $sentiment): static
    {
        $this->sentiment = $sentiment;
        return $this;
    }

    public function getTypeReclamation(): ?string
    {
        return $this->typeReclamation;
    }

    public function setTypeReclamation(string $typeReclamation): static
    {
        $this->typeReclamation = $typeReclamation;
        return $this;
    }

    public function isUrgent(): ?bool
    {
        return $this->urgent;
    }

    public function setUrgent(bool $urgent): static
    {
        $this->urgent = $urgent;
        return $this;
    }

    public function isFollowUp(): ?bool
    {
        return $this->followUp;
    }

    public function setFollowUp(bool $followUp): static
    {
        $this->followUp = $followUp;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    // --- CORRECTED GETTER/SETTER FOR RESPONSE ---
    public function getReponse(): ?Reponse // Changed name
    {
        return $this->reponse; // Changed property
    }

    public function setReponse(?Reponse $reponse): static // Changed name and allow null for removing response
    {
        // set the owning side of the relation if necessary
        // This logic is usually handled if Reponse is the owning side.
        // For a OneToOne where Reclamation is the inverse side, this setter is simpler.
        // If $reponse is not null and this reclamation is not already set on it, set it.
        if ($reponse !== null && $reponse->getReclamation() !== $this) {
            $reponse->setReclamation($this);
        }

        $this->reponse = $reponse;
        return $this;
    }
}