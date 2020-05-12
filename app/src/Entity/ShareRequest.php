<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ShareRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;

/**
 * @ORM\Entity(repositoryClass=ShareRequestRepository::class)
 */
class ShareRequest
{
    public const STATUS_CREATED = 'created';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';      // by receiver
    public const STATUS_CANCELLED = 'cancelled';    // by sender

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=user::class, inversedBy="SharedRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender_id;

    /**
     * @ORM\ManyToOne(targetEntity=user::class, inversedBy="receivedRequests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $receiver_id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $phone_nr;

    /**
     * @ORM\Column(type="string", length=55)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSenderId(): ?user
    {
        return $this->sender_id;
    }

    public function setSenderId(?user $sender_id): self
    {
        $this->sender_id = $sender_id;

        return $this;
    }

    public function getReceiverId(): ?user
    {
        return $this->receiver_id;
    }

    public function setReceiverId(?user $receiver_id): self
    {
        $this->receiver_id = $receiver_id;

        return $this;
    }

    public function getPhoneNr(): ?string
    {
        return $this->phone_nr;
    }

    public function setPhoneNr(string $phone_nr): self
    {
        $this->phone_nr = $phone_nr;

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

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
