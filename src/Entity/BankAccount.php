<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'bank_account')]
class BankAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $account_number = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $bank_code = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $bank_name = null;

    #[ORM\Column(type: 'string', length: 34, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(type: 'string', length: 11, nullable: true)]
    private ?string $swift = null;

    #[ORM\Column(type: 'boolean')]
    private bool $is_default = false;

    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'bankAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountNumber(): ?string
    {
        return $this->account_number;
    }

    public function setAccountNumber(string $account_number): self
    {
        $this->account_number = $account_number;
        return $this;
    }

    public function getBankCode(): ?string
    {
        return $this->bank_code;
    }

    public function setBankCode(?string $bank_code): self
    {
        $this->bank_code = $bank_code;
        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bank_name;
    }

    public function setBankName(?string $bank_name): self
    {
        $this->bank_name = $bank_name;
        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): self
    {
        $this->iban = $iban;
        return $this;
    }

    public function getSwift(): ?string
    {
        return $this->swift;
    }

    public function setSwift(?string $swift): self
    {
        $this->swift = $swift;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function setIsDefault(bool $is_default): self
    {
        $this->is_default = $is_default;
        return $this;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): self
    {
        $this->supplier = $supplier;
        return $this;
    }

    public function getFullAccountNumber(): string
    {
        if ($this->bank_code) {
            return $this->account_number . '/' . $this->bank_code;
        }
        return $this->account_number;
    }

    public function __toString(): string
    {
        $result = $this->getFullAccountNumber();
        if ($this->bank_name) {
            $result .= ' (' . $this->bank_name . ')';
        }
        return $result;
    }
}
