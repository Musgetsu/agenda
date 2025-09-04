<?php

namespace App\Entity;

use App\Repository\YearsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: YearsRepository::class)]
class Years
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $number = null;

    /**
     * @var Collection<int, Months>
     */
    #[ORM\OneToMany(targetEntity: Months::class, mappedBy: 'year')]
    private Collection $months;

    public function __construct()
    {
        $this->months = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return Collection<int, Months>
     */
    public function getMonths(): Collection
    {
        return $this->months;
    }

    public function addMonth(Months $month): static
    {
        if (!$this->months->contains($month)) {
            $this->months->add($month);
            $month->setYear($this);
        }

        return $this;
    }

    public function removeMonth(Months $month): static
    {
        if ($this->months->removeElement($month)) {
            // set the owning side to null (unless already changed)
            if ($month->getYear() === $this) {
                $month->setYear(null);
            }
        }

        return $this;
    }
}
