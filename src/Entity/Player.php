<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 */
class Player
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $color;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\game")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

    /**
     * @ORM\Column(type="boolean")
     */
    private $game_creator;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $time_left;

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
}
