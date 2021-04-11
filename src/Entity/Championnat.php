<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Cocur\Slugify\Slugify;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChampionnatRepository")
 * @ORM\Table(
 *     name="prive_championnat",
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_champ", columns={"nom"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"nom"}
 * )
 */
class Championnat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_championnat", nullable=false)
     */
    private $idChampionnat;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le nom doit contenir au moins {{ limit }} caractères",
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="string", name="nom", nullable=false, length=50)
     */
    private $nom;

    /**
     * @var int
     *
     * @Assert\GreaterThanOrEqual(
     *     value = 1,
     *     message = "La limite de brûlage doit être supérieure à {{ limit }}"
     * )
     *
     * @Assert\LessThanOrEqual(
     *     value = 4,
     *     message = "La limite de brûlage doit être inférierue à {{ limit }}"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="integer", name="limite_brulage", nullable=false)
     */
    private $limiteBrulage;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="j2rule", nullable=false)
     */
    private $j2Rule;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rencontre", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $rencontres;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Division", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $divisions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Disponibilite", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $dispos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Equipe", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $equipes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Journee", mappedBy="idChampionnat", cascade={"remove"}, orphanRemoval=true)
     */
    private $journees;

    /**
     * @return int
     */
    public function getLimiteBrulage(): int
    {
        return $this->limiteBrulage;
    }

    /**
     * @param int|null $limiteBrulage
     * @return Championnat
     */
    public function setLimiteBrulage(?int $limiteBrulage): self
    {
        $this->limiteBrulage = $limiteBrulage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJournees()
    {
        return $this->journees;
    }

    /**
     * @param mixed $journees
     * @return Championnat
     */
    public function setJournees($journees): self
    {
        $this->journees = $journees;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDispos()
    {
        return $this->dispos;
    }

    /**
     * @param mixed $dispos
     * @return Championnat
     */
    public function setDispos($dispos): self
    {
        $this->dispos = $dispos;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEquipes()
    {
        return $this->equipes;
    }

    /**
     * @param mixed $equipes
     * @return Championnat
     */
    public function setEquipes($equipes): self
    {
        $this->equipes = $equipes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDivisions()
    {
        return $this->divisions;
    }

    /**
     * @param mixed $divisions
     * @return Championnat
     */
    public function setDivisions($divisions): self
    {
        $this->divisions = $divisions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRencontres()
    {
        return $this->rencontres;
    }

    /**
     * @param mixed $rencontres
     * @return Championnat
     */
    public function setRencontres($rencontres): self
    {
        $this->rencontres = $rencontres;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdChampionnat()
    {
        return $this->idChampionnat;
    }

    /**
     * @param mixed $idChampionnat
     * @return Championnat
     */
    public function setIdChampionnat($idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return (new Slugify())->slugify($this->nom);
    }

    /**
     * @param string|null $nom
     * @return Championnat
     */
    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return bool
     */
    public function isJ2Rule(): bool
    {
        return $this->j2Rule;
    }

    /**
     * @param bool $j2Rule
     * @return Championnat
     */
    public function setJ2Rule(bool $j2Rule): self
    {
        $this->j2Rule = $j2Rule;
        return $this;
    }
}