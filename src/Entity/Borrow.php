<?php

namespace App\Entity;

use App\Repository\BorrowRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BorrowRepository::class)
 */
class Borrow
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
    private $bookName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $borrowAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $returnAt;

    /**
     * @ORM\ManyToOne(targetEntity=Book::class, inversedBy="borrow")
     * @ORM\JoinColumn(nullable=false)
     */
    private $book;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookName(): ?string
    {
        return $this->bookName;
    }

    public function setBookName(string $bookName): self
    {
        $this->bookName = $bookName;

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

    public function getBorrowAt(): ?\DateTimeInterface
    {
        return $this->borrowAt;
    }

    public function setBorrowAt(?\DateTimeInterface $borrowAt): self
    {
        $this->borrowAt = $borrowAt;

        return $this;
    }

    public function getReturnAt(): ?\DateTimeInterface
    {
        return $this->returnAt;
    }

    public function setReturnAt(?\DateTimeInterface $returnAt): self
    {
        $this->returnAt = $returnAt;

        return $this;
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
}
