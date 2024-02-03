<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\PlayerRepository")]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 20)]
    private $color;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Game")]
    #[ORM\JoinColumn(nullable: false)]
    private $game;

    #[ORM\Column(type: "boolean")]
    private $game_creator;

    #[ORM\Column(type: "integer", nullable: true)]
    private $time_left;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: Moves::class, orphanRemoval: true)]
    private Collection $moves;

    public function __construct()
    {
        $this->moves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getIdGame(): ?game
    {
        return $this->game;
    }

    public function setIdGame(?game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getGameCreator(): ?bool
    {
        return $this->game_creator;
    }

    public function setGameCreator(bool $game_creator): self
    {
        $this->game_creator = $game_creator;

        return $this;
    }

    public function getTimeLeft(): ?int
    {
        return $this->time_left;
    }

    public function setTimeLeft(?int $time_left): self
    {
        $this->time_left = $time_left;

        return $this;
    }

    public function isGameCreator(): ?bool
    {
        return $this->game_creator;
    }

    public function getGame(): ?game
    {
        return $this->game;
    }

    public function setGame(?game $game): static
    {
        $this->game = $game;

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
            $move->setPlayer($this);
        }

        return $this;
    }

    public function removeMove(Moves $move): static
    {
        if ($this->moves->removeElement($move)) {
            // set the owning side to null (unless already changed)
            if ($move->getPlayer() === $this) {
                $move->setPlayer(null);
            }
        }

        return $this;
    }
}
