<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'service')]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\ManyToOne(targetEntity: BankAccount::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?BankAccount $bankAccount = null;

    #[ORM\Column(type: 'integer', options: ['comment' => 'Den v měsíci kdy se vytvoří faktura (1-31)'])]
    private ?int $invoiceDay = null;

    #[ORM\Column(type: 'integer', options: ['comment' => 'Počet dní od vytvoření do splatnosti'])]
    private ?int $dueDays = null;

    #[ORM\Column(type: 'string', length: 20, options: ['comment' => 'Frekvence: monthly, quarterly, yearly'])]
    private ?string $frequency = null;

    #[ORM\Column(type: 'date', nullable: true, options: ['comment' => 'Datum posledního vygenerování faktury'])]
    private ?\DateTimeInterface $lastInvoiceDate = null;

    #[ORM\Column(type: 'date', nullable: true, options: ['comment' => 'Datum kdy služba začíná'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'date', nullable: true, options: ['comment' => 'Datum kdy služba končí (null = nekonečně)'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: ServiceItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->isActive = true;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccount $bankAccount): self
    {
        $this->bankAccount = $bankAccount;
        return $this;
    }

    public function getInvoiceDay(): ?int
    {
        return $this->invoiceDay;
    }

    public function setInvoiceDay(int $invoiceDay): self
    {
        $this->invoiceDay = $invoiceDay;
        return $this;
    }

    public function getDueDays(): ?int
    {
        return $this->dueDays;
    }

    public function setDueDays(int $dueDays): self
    {
        $this->dueDays = $dueDays;
        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): self
    {
        $this->frequency = $frequency;
        return $this;
    }

    public function getLastInvoiceDate(): ?\DateTimeInterface
    {
        return $this->lastInvoiceDate;
    }

    public function setLastInvoiceDate(?\DateTimeInterface $lastInvoiceDate): self
    {
        $this->lastInvoiceDate = $lastInvoiceDate;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(ServiceItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setService($this);
        }
        return $this;
    }

    public function removeItem(ServiceItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getService() === $this) {
                $item->setService(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Zjistí, jestli je čas vytvořit novou fakturu
     */
    public function shouldCreateInvoice(\DateTime $currentDate = null): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($currentDate === null) {
            $currentDate = new \DateTime();
        }

        // Zkontrolovat, jestli služba už začala
        if ($this->startDate && $currentDate < $this->startDate) {
            return false;
        }

        // Zkontrolovat, jestli služba už neskončila
        if ($this->endDate && $currentDate > $this->endDate) {
            return false;
        }

        // Zkontrolovat, jestli je správný den v měsíci
        if ((int)$currentDate->format('j') !== $this->invoiceDay) {
            return false;
        }

        // Zkontrolovat, jestli už nebyla faktura vytvořena v tomto období
        if ($this->lastInvoiceDate) {
            $nextInvoiceDate = $this->calculateNextInvoiceDate($this->lastInvoiceDate);
            if ($currentDate < $nextInvoiceDate) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vypočítá datum další faktury na základě frekvence
     */
    private function calculateNextInvoiceDate(\DateTimeInterface $lastDate): \DateTime
    {
        $nextDate = new \DateTime($lastDate->format('Y-m-d'));
        
        switch ($this->frequency) {
            case 'monthly':
                $nextDate->modify('+1 month');
                break;
            case 'quarterly':
                $nextDate->modify('+3 months');
                break;
            case 'yearly':
                $nextDate->modify('+1 year');
                break;
        }

        return $nextDate;
    }

    /**
     * Vypočítá datum splatnosti faktury
     */
    public function calculateDueDate(\DateTime $invoiceDate): \DateTime
    {
        $dueDate = clone $invoiceDate;
        $dueDate->modify('+' . $this->dueDays . ' days');
        return $dueDate;
    }
}
