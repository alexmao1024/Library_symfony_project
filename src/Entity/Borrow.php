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
     * @ORM\Column(type="float", nullable=true)
     */
    private $spend;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ISBN;

    /**
     * @ORM\ManyToOne(targetEntity=NormalUser::class, inversedBy="borrows")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $borrower;


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


    public function getSpend(): ?float
    {
        return $this->spend;
    }

    public function setSpend(?float $spend): self
    {
        $this->spend = $spend;

        return $this;
    }

    public function getISBN(): ?string
    {
        return $this->ISBN;
    }

    public function setISBN(string $ISBN): self
    {
        $this->ISBN = $ISBN;

        return $this;
    }

    public function getBorrower(): ?NormalUser
    {
        return $this->borrower;
    }

    public function setBorrower(?NormalUser $borrower): self
    {
        $this->borrower = $borrower;

        return $this;
    }

}
