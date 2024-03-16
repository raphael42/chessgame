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

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $winner = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $end_reason = null;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Messages::class, orphanRemoval: true)]
    private Collection $messages;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pgn = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column(length: 20)]
    private ?string $creator_color_chose = null;

    public function __construct()
    {
        $this->moves = new ArrayCollection();
        $this->messages = new ArrayCollection();
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getWinner(): ?string
    {
        return $this->winner;
    }

    public function setWinner(?string $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function getEndReason(): ?string
    {
        return $this->end_reason;
    }

    public function setEndReason(?string $end_reason): static
    {
        $this->end_reason = $end_reason;

        return $this;
    }

    /**
     * @return Collection<int, Messages>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messages $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setGame($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getGame() === $this) {
                $message->setGame(null);
            }
        }

        return $this;
    }

    public function getPgn(): ?string
    {
        return $this->pgn;
    }

    public function setPgn(?string $pgn): static
    {
        $this->pgn = $pgn;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatorColorChose(): ?string
    {
        return $this->creator_color_chose;
    }

    public function setCreatorColorChose(string $creator_color_chose): static
    {
        $this->creator_color_chose = $creator_color_chose;

        return $this;
    }
}
