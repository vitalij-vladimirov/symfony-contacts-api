<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTimeImmutable;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="bigint", length=15, unique=true)
     */
    private int $phoneNr;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
    * @ORM\Column(type="string", unique=true, nullable=true)
    */
    private string $apiToken;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private DateTimeImmutable $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Contact::class, mappedBy="user_id", orphanRemoval=true)
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity=ShareRequest::class, mappedBy="sender_id")
     */
    private $sharedRequests;

    /**
     * @ORM\OneToMany(targetEntity=ShareRequest::class, mappedBy="receiver_id")
     */
    private $receivedRequests;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->sharedRequests = new ArrayCollection();
        $this->receivedRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): int
    {
        return (int) $this->phoneNr;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

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

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setUserId($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
            // set the owning side to null (unless already changed)
            if ($contact->getUserId() === $this) {
                $contact->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ShareRequest[]
     */
    public function getSharedRequests(): Collection
    {
        return $this->sharedRequests;
    }

    public function addSharedRequest(ShareRequest $sharedRequest): self
    {
        if (!$this->sharedRequests->contains($sharedRequest)) {
            $this->sharedRequests[] = $sharedRequest;
            $sharedRequest->setSender($this);
        }

        return $this;
    }

    public function removeSharedRequest(ShareRequest $sharedRequest): self
    {
        if ($this->sharedRequests->contains($sharedRequest)) {
            $this->sharedRequests->removeElement($sharedRequest);
            // set the owning side to null (unless already changed)
            if ($sharedRequest->getSenderId() === $this) {
                $sharedRequest->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ShareRequest[]
     */
    public function getReceivedRequests(): Collection
    {
        return $this->receivedRequests;
    }

    public function addReceivedRequest(ShareRequest $receivedRequest): self
    {
        if (!$this->receivedRequests->contains($receivedRequest)) {
            $this->receivedRequests[] = $receivedRequest;
            $receivedRequest->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedRequest(ShareRequest $receivedRequest): self
    {
        if ($this->receivedRequests->contains($receivedRequest)) {
            $this->receivedRequests->removeElement($receivedRequest);
            // set the owning side to null (unless already changed)
            if ($receivedRequest->getReceive() === $this) {
                $receivedRequest->setReceiver(null);
            }
        }

        return $this;
    }
}
