<?php
// src/Entity/User.php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM; // Keep this import for ORM namespace access in attributes
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; // Keep this import for validator attribute
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

// Use #[ORM\...] attributes directly above the class
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 100, name: "full_name")]
    private ?string $fullName = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true, name: "phone_number")]
    private ?string $phoneNumber = null;

    // Maps directly to the ENUM as a string in the database
    #[ORM\Column(type: 'string', length: 50)]
    private ?string $role = null;

    // Relationships use #[ORM\...] attributes
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'author')]
    private Collection $authoredArticles;

    #[ORM\OneToMany(targetEntity: Consultation::class, mappedBy: 'patient')]
    private Collection $patientConsultations;

    #[ORM\OneToMany(targetEntity: Consultation::class, mappedBy: 'psychiatrist')]
    private Collection $psychiatristConsultations;

    /**
     * @var Collection<int, Reclamation>
     */
    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'user')]
    private Collection $reclamations;

    public function __construct()
    {
        $this->authoredArticles = new ArrayCollection();
        $this->patientConsultations = new ArrayCollection();
        $this->psychiatristConsultations = new ArrayCollection();
        $this->reclamations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // Symfony Security UserInterface methods
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @deprecated since Symfony 5.3
     */
    public function getUsername(): string
    {
         return (string) $this->username;
    }


    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    // Symfony Security PasswordAuthenticatedUserInterface method
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    // Symfony Security UserInterface method
    public function getRoles(): array
    {
        // Assuming the 'role' column stores a single role like 'ADMIN', 'PSYCHIATRIST', 'PATIENT'
        // Convert it into the expected array format for Symfony Security (e.g., ['ROLE_ADMIN'])
        $roles = [$this->role ? 'ROLE_' . strtoupper($this->role) : ''];

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

     // Symfony Security UserInterface method
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getAuthoredArticles(): Collection
    {
        return $this->authoredArticles;
    }

    public function addAuthoredArticle(Article $authoredArticle): static
    {
        if (!$this->authoredArticles->contains($authoredArticle)) {
            $this->authoredArticles->add($authoredArticle);
            $authoredArticle->setAuthor($this);
        }

        return $this;
    }

    public function removeAuthoredArticle(Article $authoredArticle): static
    {
        if ($this->authoredArticles->removeElement($authoredArticle)) {
            // set the owning side to null (unless already changed)
            if ($authoredArticle->getAuthor() === $this) {
                $authoredArticle->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Consultation>
     */
    public function getPatientConsultations(): Collection
    {
        return $this->patientConsultations;
    }

    public function addPatientConsultation(Consultation $patientConsultation): static
    {
        if (!$this->patientConsultations->contains($patientConsultation)) {
            $this->patientConsultations->add($patientConsultation);
            $patientConsultation->setPatient($this);
        }

        return $this;
    }

    public function removePatientConsultation(Consultation $patientConsultation): static
    {
        if ($this->patientConsultations->removeElement($patientConsultation)) {
            // set the owning side to null (unless already changed)
            if ($patientConsultation->getPatient() === $this) {
                $patientConsultation->setPatient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Consultation>
     */
    public function getPsychiatristConsultations(): Collection
    {
        return $this->psychiatristConsultations;
    }

    public function addPsychiatristConsultation(Consultation $psychiatristConsultation): static
    {
        if (!$this->psychiatristConsultations->contains($psychiatristConsultation)) {
            $this->psychiatristConsultations->add($psychiatristConsultation);
            $psychiatristConsultation->setPsychiatrist($this);
        }

        return $this;
    }

    public function removePsychiatristConsultation(Consultation $psychiatristConsultation): static
    {
        if ($this->psychiatristConsultations->removeElement($psychiatristConsultation)) {
            // set the owning side to null (unless already changed)
            if ($psychiatristConsultation->getPsychiatrist() === $this) {
                $psychiatristConsultation->setPsychiatrist(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): static
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations->add($reclamation);
            $reclamation->setUser($this);
        }

        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): static
    {
        if ($this->reclamations->removeElement($reclamation)) {
            // set the owning side to null (unless already changed)
            if ($reclamation->getUser() === $this) {
                $reclamation->setUser(null);
            }
        }

        return $this;
    }
}