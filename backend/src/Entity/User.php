<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $login = null;

    #[ORM\Column(nullable: false)]
    private ?bool $isVerified = false;

    /**
     * @var Collection<int, AuthCode>
     */
    #[ORM\OneToMany(targetEntity: AuthCode::class, mappedBy: 'user')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $authCodes;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->authCodes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, AuthCode>
     */
    public function getAuthCodes(): Collection
    {
        return $this->authCodes;
    }

    public function addAuthCode(AuthCode $authCode): static
    {
        if (!$this->authCodes->contains($authCode)) {
            $this->authCodes->add($authCode);
            $authCode->setUser($this);
        }

        return $this;
    }

    public function removeAuthCode(AuthCode $authCode): static
    {
        $this->authCodes->removeElement($authCode);

        return $this;
    }

    public function getLatestAuthCode(): ?AuthCode
    {
        return $this->authCodes->isEmpty() ? null : $this->authCodes->first();
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return null; 
    }

    public function eraseCredentials(): void
    {
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }
}
