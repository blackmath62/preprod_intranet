<?php

namespace App\Entity\Main;

use App\Repository\Main\ControlesAnomaliesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ControlesAnomaliesRepository::class)]
class ControlesAnomalies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]

    private $id;

    #[ORM\Column(type: "string", length: 255)]
    private $idAnomalie;

    #[ORM\Column(type: "datetime")]
    private $createdAt;

    #[ORM\Column(type: "string", length: 255)]
    private $type;

    #[ORM\Column(type: "datetime")]
    private $modifiedAt;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $updatedAt;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdAnomalie(): ?string
    {
        return $this->idAnomalie;
    }

    public function setIdAnomalie(string $idAnomalie): self
    {
        $this->idAnomalie = $idAnomalie;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeInterface $modifiedAt) : self
    {
        $this->modifiedAt = $modifiedAt;

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

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }
}
