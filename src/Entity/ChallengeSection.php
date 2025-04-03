<?php

namespace App\Entity;

use App\Repository\ChallengeSectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChallengeSectionRepository::class)]
class ChallengeSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ordering = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    /**
     * @var Collection<int, ChallengeCategory>
     */
    #[ORM\OneToMany(targetEntity: ChallengeCategory::class, mappedBy: 'challengeSection')]
    private Collection $challengeCategories;

    public function __construct()
    {
        $this->challengeCategories = new ArrayCollection();
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, ChallengeCategory>
     */
    public function getChallengeCategories(): Collection
    {
        return $this->challengeCategories;
    }

    public function addChallengeCategory(ChallengeCategory $challengeCategory): static
    {
        if (!$this->challengeCategories->contains($challengeCategory)) {
            $this->challengeCategories->add($challengeCategory);
            $challengeCategory->setChallengeSection($this);
        }

        return $this;
    }

    public function removeChallengeCategory(ChallengeCategory $challengeCategory): static
    {
        if ($this->challengeCategories->removeElement($challengeCategory)) {
            // set the owning side to null (unless already changed)
            if ($challengeCategory->getChallengeSection() === $this) {
                $challengeCategory->setChallengeSection(null);
            }
        }

        return $this;
    }
}
