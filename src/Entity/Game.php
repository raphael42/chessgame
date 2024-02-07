<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\GameRepository")]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 200)]
    private $url;

    #[ORM\Column(type: "integer")]
    private $increment;

    #[ORM\Column(type: "string", length: 255)]
    private $fen;

    #[ORM\Column(nullable: true)]
    private ?float $time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_insert = null;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Moves::class, orphanRemoval: true)]
    private Collection $moves;

    public function __construct()
    {
        $this->moves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getIncrement(): ?int
    {
        return $this->increment;
    }

    public function setIncrement(int $increment): self
    {
        $this->increment = $increment;

        return $this;
    }

    public function getFen(): ?string
    {
        return $this->fen;
    }

    public function setFen(string $fen): self
    {
        $this->fen = $fen;

        return $this;
    }

    public function getTime(): ?float
    {
        return $this->time;
    }

    public function setTime(?float $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getDateInsert(): ?\DateTimeInterface
    {
        return $this->date_insert;
    }

    public function setDateInsert(\DateTimeInterface $date_insert): static
    {
        $this->date_insert = $date_insert;

        return $this;
    }

    /**
     * @return Collection<int, Moves>
     */
    public function getMoves(): Collection
    {
        return $this->moves;
    }

    public function addMove(Moves $move): static
    {
        if (!$this->moves->contains($move)) {
            $this->moves->add($move);
            $move->setGame($this);
        }

        return $this;
    }

    public function removeMove(Moves $move): static
    {
        if ($this->moves->removeElement($move)) {
            // set the owning side to null (unless already changed)
            if ($move->getGame() === $this) {
                $move->setGame(null);
            }
        }

        return $this;
    }
}
