<?php

namespace App\Entity;

use App\Repository\MonthsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MonthsRepository::class)]
class Months
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'months')]
    #[ORM\JoinColumn(nullable: false)]
    private ?years $year = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\Column]
    private ?int $size = null;

    /**
     * @var Collection<int, Days>
     */
    #[ORM\OneToMany(targetEntity: Days::class, mappedBy: 'month')]
    private Collection $days;

    public function __construct()
    {
        $this->days = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getYear(): ?years
    {
        return $this->year;
    }

    public function setYear(?years $year): static
    {
        $this->year = $year;

        return $this;
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

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return Collection<int, Days>
     */
    public function getDays(): Collection
    {
        return $this->days;
    }

    public function addDay(Days $day): static
    {
        if (!$this->days->contains($day)) {
            $this->days->add($day);
            $day->setMonth($this);
        }

        return $this;
    }

    public function removeDay(Days $day): static
    {
        if ($this->days->removeElement($day)) {
            // set the owning side to null (unless already changed)
            if ($day->getMonth() === $this) {
                $day->setMonth(null);
            }
        }

        return $this;
    }
}
