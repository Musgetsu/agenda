<?php

namespace App\Entity;

use App\Repository\RendezvousRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RendezvousRepository::class)]
class Rendezvous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Slots $slot = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $def = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlot(): ?Slots
    {
        return $this->slot;
    }

    public function setSlot(Slots $slot): static
    {
        $this->slot = $slot;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getDef(): ?string
    {
        return $this->def;
    }

    public function setDef(string $def): static
    {
        $this->def = $def;

        return $this;
    }
}
