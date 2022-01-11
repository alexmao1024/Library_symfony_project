<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=NormalUser::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $normalUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getNormalUser(): ?NormalUser
    {
        return $this->normalUser;
    }

    public function setNormalUser(?NormalUser $normalUser): self
    {
        $this->normalUser = $normalUser;

        return $this;
    }
}
