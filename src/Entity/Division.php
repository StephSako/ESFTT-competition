<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DivisionRepository")
 * @ORM\Table(
 *     name="prive_division",
 *     indexes={
 *         @Index(name="IDX_div_champ", columns={"id_championnat"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_div_sn", columns={"short_name", "id_championnat"}),
 *          @UniqueConstraint(name="UNIQ_div_ln", columns={"long_name",  "id_championnat"})
 *     }
 * )
 */
class Division
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id_division", nullable=false)
     */
    private $idDivision;

    /**
     * @var Championnat
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Championnat", inversedBy="divisions")
     * @ORM\JoinColumn(name="id_championnat", referencedColumnName="id_championnat", nullable=false)
     */
    private $idChampionnat;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 5,
     *      minMessage = "Le diminitif doit contenir au moins {{ limit }} lettres",
     *      maxMessage = "Le diminitif doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="string", name="short_name", nullable=false, length=2)
     */
    private $shortName;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 25,
     *      minMessage = "Le nom doit contenir au moins {{ limit }} lettres",
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} lettres"
     * )
     *
     * @ORM\Column(type="string", name="long_name", nullable=false, length=25)
     */
    private $longName;

    /**
     * @var int
     *
     * @Assert\GreaterThanOrEqual(
     *     value = -1,
     *     message = "Indiquez -1 si division inexistante pour ce championnat"
     * )
     *
     * @Assert\NotEqualTo(
     *     value = 0,
     *     message = "Indiquez -1 si division inexistante pour ce championnat "
     * )
     *
     * @Assert\LessThanOrEqual(
     *     value = 9,
     *     message = "Le nombre maximal de joueurs est {{ value }}"
     * )
     *
     * @Assert\NotBlank(
     *     normalizer="trim"
     *)
     *
     * @ORM\Column(type="integer", name="nb_joueurs", nullable=false)
     */
    private $nbJoueurs;

    /**
     * @var Equipe[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Equipe", mappedBy="idDivision")
     */
    protected $equipes;

    /**
     * @return mixed
     */
    public function getIdDivision()
    {
        return $this->idDivision;
    }

    /**
     * @param mixed $idDivision
     * @return Division
     */
    public function setIdDivision($idDivision): self
    {
        $this->idDivision = $idDivision;
        return $this;
    }

    /**
     * @return Championnat
     */
    public function getIdChampionnat(): Championnat
    {
        return $this->idChampionnat;
    }

    /**
     * @param Championnat $idChampionnat
     * @return Division
     */
    public function setIdChampionnat(Championnat $idChampionnat): self
    {
        $this->idChampionnat = $idChampionnat;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string|null $shortName
     * @return Division
     */
    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongName(): string
    {
        return $this->longName;
    }

    /**
     * @param string|null $longName
     * @return Division
     */
    public function setLongName(?string $longName): self
    {
        $this->longName = $longName;
        return $this;
    }

    /**
     * @return Equipe[]
     */
    public function getEquipes(): array
    {
        return $this->equipes;
    }

    /**
     * @param Equipe[] $equipes
     * @return Division
     */
    public function setEquipes(array $equipes): self
    {
        $this->equipes = $equipes;
        return $this;
    }

    /**
     * @return int
     */
    public function getNbJoueurs(): int
    {
        return $this->nbJoueurs;
    }

    /**
     * @param int $nbJoueurs
     * @return Division
     */
    public function setNbJoueurs(int $nbJoueurs): self
    {
        $this->nbJoueurs = $nbJoueurs;
        return $this;
    }
}