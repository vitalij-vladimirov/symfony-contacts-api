<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ShareRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * @ORM\Entity(repositoryClass=ShareRequestRepository::class)
 */
class ShareRequest
{
    public const STATUS_CREATED = 'created';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';      // by receiver
    public const STATUS_CANCELLED = 'cancelled';    // by sender

    public const STATUS_LIST = [
        self::STATUS_CREATED,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=user::class, inversedBy="SharedRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $sender;

    /**
     * @ORM\ManyToOne(targetEntity=user::class, inversedBy="receivedRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $receiver;

    /**
     * @ORM\Column(type="bigint")
     */
    private int $phoneNr;

    /**
     * @ORM\Column(type="string", length=55)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private string $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(User $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getPhoneNr(): ?int
    {
        return $this->phoneNr;
    }

    public function setPhoneNr(int $phoneNr): self
    {
        $this->phoneNr = $phoneNr;

        return $this;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
