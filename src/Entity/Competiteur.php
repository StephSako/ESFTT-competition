<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompetiteurRepository")
 * @ORM\Table(name="competiteur")
 */
class Competiteur implements UserInterface, Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_competiteur")
     */
    private $idCompetiteur;

    /**
     * @ORM\Column(name="license", type="integer")
     */
    private $license;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, name="nom")
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=250, nullable=false)
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 250,
     *      minMessage = "Votre mot de passe doit contenir au moins {{ limit }} letttres",
     *      maxMessage = "Votre mot de passe doit contenir au maximum {{ limit }} letttres",
     *     )
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=50, nullable=false)
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 30,
     *      minMessage = "Votre pseudo doit contenir au moins {{ limit }} letttres",
     *      maxMessage = "Votre pseudo doit contenir au maximum {{ limit }} letttres",
     *     )
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=1, name="role")
     */
    private $role;

    /**
     * @return int|null
     */
    public function getIdCompetiteur(): ?int
    {
        return $this->idCompetiteur;
    }

    /**
     * @return int|null
     */
    public function getLicense(): ?int
    {
        return $this->license;
    }

    /**
     * @param int $license
     * @return $this
     */
    public function setLicense(int $license): self
    {
        $this->license = $license;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     * @return $this
     */
    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return $this
     */
    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getRoles()
    {
        if ($this->getRole() == 'C') {
            return ['ROLE_CAPITAINE', 'ROLE_JOUEUR'];
        } else {
            return ['ROLE_JOUEUR'];
        }
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    { }

    public function serialize()
    {
        return serialize([
            $this->idCompetiteur,
            $this->username,
            $this->password
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->idCompetiteur,
            $this->username,
            $this->password
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }
}