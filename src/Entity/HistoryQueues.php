<?php

namespace App\Entity;

use App\Repository\HistoryQueuesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HistoryQueuesRepository::class)
 */
class HistoryQueues
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
    private $customerId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $customerName;

    /**
     * @ORM\Column(type="integer")
     */
    private $queueNumber;

    /**
     * @ORM\Column(type="datetime")
     */
    private $admissionDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $attentionStart;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): self
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getQueueNumber(): ?int
    {
        return $this->queueNumber;
    }

    public function setQueueNumber(int $queueNumber): self
    {
        $this->queueNumber = $queueNumber;

        return $this;
    }

    public function getAdmissionDate(): ?\DateTimeInterface
    {
        return $this->admissionDate;
    }

    public function setAdmissionDate(\DateTimeInterface $admissionDate): self
    {
        $this->admissionDate = $admissionDate;

        return $this;
    }

    public function getAttentionStart(): ?\DateTimeInterface
    {
        return $this->attentionStart;
    }

    public function setAttentionStart(?\DateTimeInterface $attentionStart): self
    {
        $this->attentionStart = $attentionStart;

        return $this;
    }
}
