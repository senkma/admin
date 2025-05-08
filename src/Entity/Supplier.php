<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'supplier')]
class Supplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $number = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $postal_code = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $invoice_email = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $dic = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $ico = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $bank_account = null;

    #[ORM\Column(type: 'boolean')]
    private bool $vat_payer = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(?string $postal_code): self
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getInvoiceEmail(): ?string
    {
        return $this->invoice_email;
    }

    public function setInvoiceEmail(?string $invoice_email): self
    {
        $this->invoice_email = $invoice_email;

        return $this;
    }

    public function getDic(): ?string
    {
        return $this->dic;
    }

    public function setDic(?string $dic): self
    {
        $this->dic = $dic;

        return $this;
    }

    public function getIco(): ?string
    {
        return $this->ico;
    }

    public function setIco(?string $ico): self
    {
        $this->ico = $ico;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBankAccount(): ?string
    {
        return $this->bank_account;
    }

    public function setBankAccount(?string $bank_account): self
    {
        $this->bank_account = $bank_account;

        return $this;
    }

    public function isVatPayer(): bool
    {
        return $this->vat_payer;
    }

    public function setVatPayer(bool $vat_payer): self
    {
        $this->vat_payer = $vat_payer;

        return $this;
    }
}