<?php

namespace App\Entity;

use App\Repository\SubscribeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubscribeRepository::class)
 */
class Subscribe
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Book::class, inversedBy="subscribes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $book;

    /**
     * @ORM\Column(type="datetime")
     */
    private $subscribeAt;

    /**
     * @ORM\OneToOne(targetEntity=NormalUser::class, inversedBy="subscribe", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $normalUser;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getSubscribeAt(): ?\DateTimeInterface
    {
        return $this->subscribeAt;
    }

    public function setSubscribeAt(\DateTimeInterface $subscribeAt): self
    {
        $this->subscribeAt = $subscribeAt;

        return $this;
    }

    public function getNormalUser(): ?NormalUser
    {
        return $this->normalUser;
    }

    public function setNormalUser(NormalUser $normalUser): self
    {
        $this->normalUser = $normalUser;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
