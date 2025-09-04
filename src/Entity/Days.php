<?php

namespace App\Entity;

use App\Repository\DaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DaysRepository::class)]
class Days
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'days')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Months $month = null;

    #[ORM\Column]
    private ?int $number = null;

    /**
     * @var Collection<int, Slots>
     */
    #[ORM\OneToMany(targetEntity: Slots::class, mappedBy: 'day')]
    private Collection $slots;

    public function __construct()
    {
        $this->slots = new ArrayCollection();
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

    public function getMonth(): ?Months
    {
        return $this->month;
    }

    public function setMonth(?Months $month): static
    {
        $this->month = $month;

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

    /**
     * @return Collection<int, Slots>
     */
    public function getSlots(): Collection
    {
        return $this->slots;
    }

    public function addSlot(Slots $slot): static
    {
        if (!$this->slots->contains($slot)) {
            $this->slots->add($slot);
            $slot->setDay($this);
        }

        return $this;
    }

    public function removeSlot(Slots $slot): static
    {
        if ($this->slots->removeElement($slot)) {
            // set the owning side to null (unless already changed)
            if ($slot->getDay() === $this) {
                $slot->setDay(null);
            }
        }

        return $this;
    }
}
