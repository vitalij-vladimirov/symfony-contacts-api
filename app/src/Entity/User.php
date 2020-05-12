<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTimeInterface;

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
    private $id;

    /**
     * @ORM\Column(type="bigint", length=15, unique=true)
     */
    private $phone_nr;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
    * @ORM\Column(type="string", unique=true, nullable=true)
    */
    private $apiToken;

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

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

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
        return $this->phone_nr;
    }

    public function setPhoneNr(int $phone_nr): self
    {
        $this->phone_nr = $phone_nr;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): int
    {
        return (int) $this->phone_nr;
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
    public function getPassword(): string
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
    public function getApiToken(): string
    {
        return (string) $this->apiToken;
    }

    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

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
            $sharedRequest->setSenderId($this);
        }

        return $this;
    }

    public function removeSharedRequest(ShareRequest $sharedRequest): self
    {
        if ($this->sharedRequests->contains($sharedRequest)) {
            $this->sharedRequests->removeElement($sharedRequest);
            // set the owning side to null (unless already changed)
            if ($sharedRequest->getSenderId() === $this) {
                $sharedRequest->setSenderId(null);
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
            $receivedRequest->setReceiverId($this);
        }

        return $this;
    }

    public function removeReceivedRequest(ShareRequest $receivedRequest): self
    {
        if ($this->receivedRequests->contains($receivedRequest)) {
            $this->receivedRequests->removeElement($receivedRequest);
            // set the owning side to null (unless already changed)
            if ($receivedRequest->getReceiverId() === $this) {
                $receivedRequest->setReceiverId(null);
            }
        }

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
