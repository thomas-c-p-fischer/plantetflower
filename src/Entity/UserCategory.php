<?php

namespace App\Entity;

use App\Repository\UserCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCategoryRepository::class)]
class UserCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $Payer = null;

    #[ORM\Column]
    private ?bool $Owner = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'userCategories')]
    private Collection $user;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isPayer(): ?bool
    {
        return $this->Payer;
    }

    public function setPayer(bool $Payer): self
    {
        $this->Payer = $Payer;

        return $this;
    }

    public function isOwner(): ?bool
    {
        return $this->Owner;
    }

    public function setOwner(bool $Owner): self
    {
        $this->Owner = $Owner;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->user->removeElement($user);

        return $this;
    }
}
