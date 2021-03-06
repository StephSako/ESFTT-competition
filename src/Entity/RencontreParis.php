<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RencontreParisRepository")
 * @ORM\Table(
 *     name="prive_rencontre_paris",
 *     indexes={
 *         @Index(name="IDX_renc_par_id_j3", columns={"id_joueur_3"}),
 *         @Index(name="IDX_renc_par_id_j7", columns={"id_joueur_7"}),
 *         @Index(name="IDX_renc_par_id_e", columns={"id_equipe"}),
 *         @Index(name="IDX_renc_par_id_j", columns={"id_journee"}),
 *         @Index(name="IDX_renc_par_id_j4", columns={"id_joueur_4"}),
 *         @Index(name="IDX_renc_par_id_j0", columns={"id_joueur_0"}),
 *         @Index(name="IDX_renc_par_id_j8", columns={"id_joueur_8"}),
 *         @Index(name="IDX_renc_par_id_j2", columns={"id_joueur_2"}),
 *         @Index(name="IDX_renc_par_id_j6", columns={"id_joueur_6"}),
 *         @Index(name="IDX_renc_par_id_j5", columns={"id_joueur_5"}),
 *         @Index(name="IDX_renc_par_id_j1", columns={"id_joueur_1"})
 *     },
 *     uniqueConstraints={
 *          @UniqueConstraint(name="UNIQ_renc_par", columns={"adversaire"})
 *     }
 * )
 * @UniqueEntity(
 *     fields={"adversaire"}
 * )
 */
class RencontreParis
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var JourneeParis
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\JourneeParis", inversedBy="rencontres")
     * @ORM\JoinColumn(name="id_journee", referencedColumnName="id_journee", nullable=false)
     */
    private $idJournee;

    /**
     * @var boolean
     *
     * @ORM\Column(name="exempt", type="boolean", nullable=false)
     */
    private $exempt;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_0", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur0;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_1", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur1;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_2", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur2;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_3", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur3;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_4", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur4;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_5", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur5;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_6", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur6;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_7", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur7;

    /**
     * @var Competiteur|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competiteur")
     * @ORM\JoinColumn(name="id_joueur_8", nullable=true, referencedColumnName="id_competiteur", onDelete="SET NULL")
     */
    private $idJoueur8;

    /**
     * @var EquipeParis
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\EquipeParis", inversedBy="rencontresParis")
     * @ORM\JoinColumn(name="id_equipe", referencedColumnName="id_equipe", nullable=false)
     */
    private $idEquipe;

    /**
     * @var boolean
     *
     * @ORM\Column(name="domicile", type="boolean", nullable=false)
     */
    private $domicile;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hosted", type="boolean", nullable=false)
     */
    private $hosted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="reporte", type="boolean", nullable=false)
     */
    private $reporte;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="date", name="date_report", nullable=false)
     */
    private $dateReport;

    /**
     * @var string|null
     *
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "L'adversaire doit contenir au maximum {{ limit }} caractères"
     * )
     *
     * @ORM\Column(name="adversaire", nullable=true, type="string", length=50)
     */
    private $adversaire;

    /**
     * @param int $n
     * @return Competiteur|null
     */
    public function getIdJoueurN(int $n): ?Competiteur
    {
        if ($n == 0) return $this->getIdJoueur0();
        else if ($n == 1) return $this->getIdJoueur1();
        else if ($n == 2) return $this->getIdJoueur2();
        else if ($n == 3) return $this->getIdJoueur3();
        else if ($n == 4) return $this->getIdJoueur4();
        else if ($n == 5) return $this->getIdJoueur5();
        else if ($n == 6) return $this->getIdJoueur6();
        else if ($n == 7) return $this->getIdJoueur7();
        else if ($n == 8) return $this->getIdJoueur8();
        else return null;
    }

    /**
     * @param int $n
     * @param $val
     * @return RencontreParis
     */
    public function setIdJoueurN(int $n, $val): self
    {
        if ($n == 0) return $this->setIdJoueur0($val);
        else if ($n == 1) return $this->setIdJoueur1($val);
        else if ($n == 2) return $this->setIdJoueur2($val);
        else if ($n == 3) return $this->setIdJoueur3($val);
        else if ($n == 4) return $this->setIdJoueur4($val);
        else if ($n == 5) return $this->setIdJoueur5($val);
        else if ($n == 6) return $this->setIdJoueur6($val);
        else if ($n == 7) return $this->setIdJoueur7($val);
        else if ($n == 8) return $this->setIdJoueur8($val);
        else return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur0(): ?Competiteur
    {
        return $this->idJoueur0;
    }

    /**
     * @param Competiteur|null $idJoueur0
     * @return RencontreParis
     */
    public function setIdJoueur0(?Competiteur $idJoueur0): RencontreParis
    {
        $this->idJoueur0 = $idJoueur0;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur1(): ?Competiteur
    {
        return $this->idJoueur1;
    }

    /**
     * @param Competiteur|null $idJoueur1
     * @return RencontreParis
     */
    public function setIdJoueur1(?Competiteur $idJoueur1): RencontreParis
    {
        $this->idJoueur1 = $idJoueur1;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur2(): ?Competiteur
    {
        return $this->idJoueur2;
    }

    /**
     * @param Competiteur|null $idJoueur2
     * @return RencontreParis
     */
    public function setIdJoueur2(?Competiteur $idJoueur2): RencontreParis
    {
        $this->idJoueur2 = $idJoueur2;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur3(): ?Competiteur
    {
        return $this->idJoueur3;
    }

    /**
     * @param Competiteur|null $idJoueur3
     * @return RencontreParis
     */
    public function setIdJoueur3(?Competiteur $idJoueur3): RencontreParis
    {
        $this->idJoueur3 = $idJoueur3;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur4(): ?Competiteur
    {
        return $this->idJoueur4;
    }

    /**
     * @param Competiteur|null $idJoueur4
     * @return RencontreParis
     */
    public function setIdJoueur4(?Competiteur $idJoueur4): RencontreParis
    {
        $this->idJoueur4 = $idJoueur4;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur5(): ?Competiteur
    {
        return $this->idJoueur5;
    }

    /**
     * @param Competiteur|null $idJoueur5
     * @return RencontreParis
     */
    public function setIdJoueur5(?Competiteur $idJoueur5): RencontreParis
    {
        $this->idJoueur5 = $idJoueur5;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur6(): ?Competiteur
    {
        return $this->idJoueur6;
    }

    /**
     * @param Competiteur|null $idJoueur6
     * @return RencontreParis
     */
    public function setIdJoueur6(?Competiteur $idJoueur6): RencontreParis
    {
        $this->idJoueur6 = $idJoueur6;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur7(): ?Competiteur
    {
        return $this->idJoueur7;
    }

    /**
     * @param Competiteur|null $idJoueur7
     * @return RencontreParis
     */
    public function setIdJoueur7(?Competiteur $idJoueur7): RencontreParis
    {
        $this->idJoueur7 = $idJoueur7;
        return $this;
    }

    /**
     * @return Competiteur|null
     */
    public function getIdJoueur8(): ?Competiteur
    {
        return $this->idJoueur8;
    }

    /**
     * @param Competiteur|null $idJoueur8
     * @return RencontreParis
     */
    public function setIdJoueur8(?Competiteur $idJoueur8): RencontreParis
    {
        $this->idJoueur8 = $idJoueur8;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDomicile(): bool
    {
        return $this->domicile;
    }

    /**
     * @param bool $domicile
     * @return RencontreParis
     */
    public function setDomicile(bool $domicile): self
    {
        $this->domicile = $domicile;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return RencontreParis
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return JourneeParis
     */
    public function getIdJournee(): JourneeParis
    {
        return $this->idJournee;
    }

    /**
     * @param JourneeParis $idJournee
     * @return RencontreParis
     */
    public function setIdJournee(JourneeParis $idJournee): self
    {
        $this->idJournee = $idJournee;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdversaire(): ?string
    {
        return $this->adversaire;
    }

    /**
     * @param string|null $adversaire
     * @return RencontreParis
     */
    public function setAdversaire(?string $adversaire): self
    {
        $this->adversaire = $adversaire;
        return $this;
    }

    /**
     * @param EquipeParis $idEquipe
     * @return RencontreParis
     */
    public function setIdEquipe(EquipeParis $idEquipe): self
    {
        $this->idEquipe = $idEquipe;
        return $this;
    }

    /**
     * @return EquipeParis
     */
    public function getIdEquipe(): EquipeParis
    {
        return $this->idEquipe;
    }

    /**
     * @return bool
     */
    public function getIsEmpty(): bool
    {
        $isEmpty = array();
        for ($i = 0; $i < $this->getIdEquipe()->getIdDivision()->getNbJoueursChampParis(); $i++){
            array_push($isEmpty, $this->getIdJoueurN($i));
        }
        return !in_array(true, $isEmpty);
    }

    /**
     * @return bool
     */
    public function getIsFull(): bool
    {
        $nbJoueursSelected = 0;
        for ($i = 0; $i < $this->getIdEquipe()->getIdDivision()->getNbJoueursChampParis(); $i++){
            if($this->getIdJoueurN($i)) $nbJoueursSelected++;
        }
        return $nbJoueursSelected == $this->getIdEquipe()->getIdDivision()->getNbJoueursChampParis();
    }

    /**
     * @return bool
     */
    public function isHosted(): bool
    {
        return $this->hosted;
    }

    /**
     * @param bool $hosted
     * @return RencontreParis
     */
    public function setHosted(bool $hosted): self
    {
        $this->hosted = $hosted;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExempt(): bool
    {
        return $this->exempt;
    }

    /**
     * @param bool $exempt
     * @return RencontreParis
     */
    public function setExempt(bool $exempt): self
    {
        $this->exempt = $exempt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReporte(): bool
    {
        return $this->reporte;
    }

    /**
     * @param bool $reporte
     * @return RencontreParis
     */
    public function setReporte(bool $reporte): self
    {
        $this->reporte = $reporte;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateReport(): DateTime
    {
        return $this->dateReport;
    }

    /**
     * @param DateTime $dateReport
     * @return RencontreParis
     */
    public function setDateReport(DateTime $dateReport): self
    {
        $this->dateReport = $dateReport;
        return $this;
    }

    /**
     * Liste des joueurs sélectionnés dans une composition d'équipe
     * @return Competiteur[]|null[]
     */
    public function getListSelectedPlayers(): array
    {
        $joueurs = array();
        for ($i = 0; $i < $this->getIdEquipe()->getIdDivision()->getNbJoueursChampParis(); $i++){
            if ($this->getIdJoueurN($i))array_push($joueurs, $this->getIdJoueurN($i));
        }
        return $joueurs;
    }

    /**
     * Liste des numéros de téléphone des joueurs sélectionnés
     * @param int $idRedacteur
     * @return string
     */
    public function getListPhoneNumbersSelectedPlayers(int $idRedacteur): string
    {
        $phoneNumbers = [];
        foreach ($this->getListSelectedPlayers() as $joueur) {
            if ($joueur->getIdCompetiteur() != $idRedacteur){
                if ($joueur->isContactablePhoneNumber() && $joueur->getPhoneNumber() && $joueur->getPhoneNumber() != "") array_push($phoneNumbers, $joueur->getPhoneNumber());
                if ($joueur->isContactablePhoneNumber2() && $joueur->getPhoneNumber2() && $joueur->getPhoneNumber2() != "") array_push($phoneNumbers, $joueur->getPhoneNumber2());
            }
        }

        return implode(",", $phoneNumbers);
    }
}