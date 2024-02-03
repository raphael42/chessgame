<?php

namespace App\Entity;

use App\Repository\MovesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovesRepository::class)]
class Moves
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'moves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\ManyToOne(inversedBy: 'moves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\Column(length: 255)]
    private ?string $fen_before = null;

    #[ORM\Column(length: 255)]
    private ?string $fen_after = null;

    #[ORM\Column(length: 10)]
    private ?string $piece = null;

    #[ORM\Column(length: 10)]
    private ?string $square_from = null;

    #[ORM\Column(length: 10)]
    private ?string $square_to = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $san = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $lan = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $flags = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getFenBefore(): ?string
    {
        return $this->fen_before;
    }

    public function setFenBefore(string $fen_before): static
    {
        $this->fen_before = $fen_before;

        return $this;
    }

    public function getFenAfter(): ?string
    {
        return $this->fen_after;
    }

    public function setFenAfter(string $fen_after): static
    {
        $this->fen_after = $fen_after;

        return $this;
    }

    public function getPiece(): ?string
    {
        return $this->piece;
    }

    public function setPiece(string $piece): static
    {
        $this->piece = $piece;

        return $this;
    }

    public function getSquareFrom(): ?string
    {
        return $this->square_from;
    }

    public function setSquareFrom(string $square_from): static
    {
        $this->square_from = $square_from;

        return $this;
    }

    public function getSquareTo(): ?string
    {
        return $this->square_to;
    }

    public function setSquareTo(string $square_to): static
    {
        $this->square_to = $square_to;

        return $this;
    }

    public function getSan(): ?string
    {
        return $this->san;
    }

    public function setSan(?string $san): static
    {
        $this->san = $san;

        return $this;
    }

    public function getLan(): ?string
    {
        return $this->lan;
    }

    public function setLan(?string $lan): static
    {
        $this->lan = $lan;

        return $this;
    }

    public function getFlags(): ?string
    {
        return $this->flags;
    }

    public function setFlags(?string $flags): static
    {
        $this->flags = $flags;

        return $this;
    }
}
