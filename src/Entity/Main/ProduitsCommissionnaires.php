<?php

namespace App\Entity\Main;

use App\Repository\Main\ProduitsCommissionnairesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitsCommissionnairesRepository::class)]
class ProduitsCommissionnaires
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "datetime")]
    private $createdAt;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $updatedAt;

    #[ORM\Column(type: "string", length: 255)]
    private $reference;

    #[ORM\Column(type: "string", length: 255)]
    private $designation;

    #[ORM\Column(type: "boolean")]
    private $contratCommissionaire;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getContratCommissionaire(): ?bool
    {
        return $this->contratCommissionaire;
    }

    public function setContratCommissionaire(bool $contratCommissionaire): self
    {
        $this->contratCommissionaire = $contratCommissionaire;

        return $this;
    }
}
