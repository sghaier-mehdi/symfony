<?php
// src/Entity/Consultation.php - Converted to #[ORM\...] Attributes

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\DBAL\Types\Types; // Keep if needed, but not used in ORM mapping attributes below
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
#[ORM\Table(name: 'consultations')]
// Add indexes if you want Doctrine to manage them (optional, DB dump already has them)
// #[ORM\Index(columns: ['patient_id'], name: 'patient_id_idx')] // Naming differs from your SQL slightly
// #[ORM\Index(columns: ['psychiatrist_id'], name: 'psychiatrist_id_idx')] // Naming differs
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // Relationship ManyToOne (Patient)
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'patientConsultations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $patient = null;

    // Relationship ManyToOne (Psychiatrist)
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'psychiatristConsultations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $psychiatrist = null;

    #[ORM\Column(type: 'datetime')] // Uses datetime, not datetime_immutable as in your original entity
    private ?\DateTimeInterface $consultationTime = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $durationMinutes = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $purpose = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $status = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, columnDefinition: "TIMESTAMP DEFAULT CURRENT_TIMESTAMP")] // Use columnDefinition for complex defaults
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true, columnDefinition: "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP")] // Use columnDefinition for complex defaults
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getPsychiatrist(): ?User
    {
        return $this->psychiatrist;
    }

    public function setPsychiatrist(?User $psychiatrist): static
    {
        $this->psychiatrist = $psychiatrist;

        return $this;
    }

    public function getConsultationTime(): ?\DateTimeInterface
    {
        return $this->consultationTime;
    }

    public function setConsultationTime(\DateTimeInterface $consultationTime): static
    {
        $this->consultationTime = $consultationTime;

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(?string $purpose): static
    {
        $this->purpose = $purpose;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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