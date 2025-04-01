<?php

namespace App\Entity;

use App\Repository\ChallengeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
class Challenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ordering = null;

    #[ORM\Column]
    private ?int $score_goal = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, ChallengeUser>
     */
    #[ORM\OneToMany(targetEntity: ChallengeUser::class, mappedBy: 'challenge', orphanRemoval: true)]
    private Collection $challengeUsers;

    #[ORM\Column(length: 255)]
    private ?string $fen = null;

    #[ORM\ManyToOne(inversedBy: 'challenges')]
    private ?ChallengeCategory $challengeCategory = null;

    public function __construct()
    {
        $this->challengeUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdering(): ?int
    {
        return $this->ordering;
    }

    public function setOrdering(int $ordering): static
    {
        $this->ordering = $ordering;

        return $this;
    }

    public function getScoreGoal(): ?int
    {
        return $this->score_goal;
    }

    public function setScoreGoal(int $score_goal): static
    {
        $this->score_goal = $score_goal;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, ChallengeUser>
     */
    public function getChallengeUsers(): Collection
    {
        return $this->challengeUsers;
    }

    public function addChallengeUser(ChallengeUser $challengeUser): static
    {
        if (!$this->challengeUsers->contains($challengeUser)) {
            $this->challengeUsers->add($challengeUser);
            $challengeUser->setChallenge($this);
        }

        return $this;
    }

    public function removeChallengeUser(ChallengeUser $challengeUser): static
    {
        if ($this->challengeUsers->removeElement($challengeUser)) {
            // set the owning side to null (unless already changed)
            if ($challengeUser->getChallenge() === $this) {
                $challengeUser->setChallenge(null);
            }
        }

        return $this;
    }

    public function getFen(): ?string
    {
        return $this->fen;
    }

    public function setFen(string $fen): static
    {
        $this->fen = $fen;

        return $this;
    }

    public function getChallengeCategory(): ?ChallengeCategory
    {
        return $this->challengeCategory;
    }

    public function setChallengeCategory(?ChallengeCategory $challengeCategory): static
    {
        $this->challengeCategory = $challengeCategory;

        return $this;
    }
}
