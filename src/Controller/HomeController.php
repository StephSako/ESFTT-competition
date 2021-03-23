<?php

namespace App\Controller;

use App\Form\RencontreDepartementaleType;
use App\Form\RencontreParisType;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\DivisionRepository;
use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $em;
    private $competiteurRepository;
    private $equipeDepartementalRepository;
    private $equipeParisRepository;
    private $disponibiliteDepartementaleRepository;
    private $disponibiliteParisRepository;
    private $journeeDepartementaleRepository;
    private $journeeParisRepository;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;
    private $divisionRepository;

    /**
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EquipeDepartementaleRepository $equipeDepartementalRepository
     * @param EquipeParisRepository $equipeParisRepository
     * @param DivisionRepository $divisionRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                RencontreParisRepository $rencontreParisRepository,
                                EquipeDepartementaleRepository $equipeDepartementalRepository,
                                EquipeParisRepository $equipeParisRepository,
                                DivisionRepository $divisionRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
        $this->competiteurRepository = $competiteurRepository;
        $this->disponibiliteDepartementaleRepository = $disponibiliteDepartementaleRepository;
        $this->disponibiliteParisRepository = $disponibiliteParisRepository;
        $this->journeeDepartementaleRepository = $journeeDepartementaleRepository;
        $this->journeeParisRepository = $journeeParisRepository;
        $this->rencontreParisRepository = $rencontreParisRepository;
        $this->equipeDepartementalRepository = $equipeDepartementalRepository;
        $this->equipeParisRepository = $equipeParisRepository;
        $this->divisionRepository = $divisionRepository;
    }

    /**
     * @Route("/", name="index")
     */
    public function indexAction(): Response
    {
        $type = ($this->get('session')->get('type') != null ? $this->get('session')->get('type') : 'departementale');
        if ($type == 'departementale') $dates = $this->journeeDepartementaleRepository->findAllDates();
        else if ($type == 'paris') $dates = $this->journeeParisRepository->findAllDates();
        else $dates = $this->journeeDepartementaleRepository->findAllDates();
        $NJournee = 1;

        while ($NJournee <= 7 && !$dates[$NJournee - 1]["undefined"] && (int) (new DateTime())->diff($dates[$NJournee - 1]["date"])->format('%R%a') < 0){
            $NJournee++;
        }

        return $this->redirectToRoute('journee.show', [
            'type' => $type,
            'id' => $NJournee
        ]);
    }

    /**
     * @param string $type
     * @param int $id
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @Route("/journee/{type}/{id}", name="journee.show")
     */
    public function journee(string $type, int $id): Response
    {
        $nbEquipes = null;
        if ($type == 'departementale') {
            // On vérifie que la journée existe
            if ((!$journee = $this->journeeDepartementaleRepository->find($id))) throw $this->createNotFoundException('Journée inexistante');
            $this->get('session')->set('type', $type);

            // Toutes les journées du type de championnat visé
            $journees = $this->journeeDepartementaleRepository->findAll();

            // Objet Disponibilité du joueur
            $dispoJoueur = $this->disponibiliteDepartementaleRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]);

            // Joueurs ayant déclaré leur disponibilité
            $joueursDeclares = $this->disponibiliteDepartementaleRepository->findJoueursDeclares($id);

            // Joueurs n'ayant pas déclaré leur disponibilité
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, $type);

            // Compositions d'équipe
            $compos = $this->rencontreDepartementaleRepository->getRencontresDepartementales($id);

            // Joueurs sélectionnées
            $selectedPlayers = $this->rencontreDepartementaleRepository->getSelectedPlayers($compos);

            // Brûlages des joueurs
            $brulages = $this->competiteurRepository->getBrulages($type, $journee->getIdJournee(), $this->equipeDepartementalRepository->findAll(), $this->divisionRepository->getMaxNbJoueursChamp($type));

            // Nombre d'équipes
            $nbEquipes = count($compos);

            // Nombre de journées
            $nbJournees = count($journees);

            // Nombre maximal de joueurs pour les compos du championnat départemental
            $nbTotalJoueurs = array_sum(array_map(function($compo) use ($type) {
                return $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampDepartementale();
            }, $compos));

            // Nombre minimal critique de joueurs pour les compos du championnat départemental
            $nbMinJoueurs = array_sum(array_map(function($compo) use ($type) {
                return $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampDepartementale() - 1;
            }, $compos));
        }
        else if ($type == 'paris') {
            if ((!$journee = $this->journeeParisRepository->find($id))) throw $this->createNotFoundException('Journée inexistante');
            $this->get('session')->set('type', $type);
            $journees = $this->journeeParisRepository->findAll();
            $dispoJoueur = $this->disponibiliteParisRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]);
            $joueursDeclares = $this->disponibiliteParisRepository->findJoueursDeclares($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, $type);
            $compos = $this->rencontreParisRepository->getRencontresParis($id);
            $selectedPlayers = $this->rencontreParisRepository->getSelectedPlayers($compos);
            $brulages = $this->competiteurRepository->getBrulages($type, $journee->getIdJournee(), $this->equipeParisRepository->findAll(), $this->divisionRepository->getMaxNbJoueursChamp($type));
            $nbEquipes = count($compos);
            $nbJournees = count($journees);
            $nbTotalJoueurs = array_sum(array_map(function($compo) use ($type) {
                return $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampParis();
            }, $compos));
            $nbMinJoueurs = array_sum(array_map(function($compo) use ($type) {
                return $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampParis() - 1;
            }, $compos));
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        $nbDispos = count(array_filter($joueursDeclares, function($dispo)
            {
                return $dispo->getDisponibilite();
            }
        ));
        $disponible = ($dispoJoueur ? $dispoJoueur->getDisponibilite() : null);
        $selected = in_array($this->getUser()->getIdCompetiteur(), $selectedPlayers);
        $allDisponibilitesDepartementales = $this->competiteurRepository->findAllDisposRecapitulatif('departementale', $this->journeeDepartementaleRepository->getNbJourneeDepartementale());
        $allDisponibiliteParis = $this->competiteurRepository->findAllDisposRecapitulatif('paris', $this->journeeParisRepository->getNbJourneeParis());

        return $this->render('journee/index.html.twig', [
            'journee' => $journee,
            'journees' => $journees,
            'nbTotalJoueurs' => $nbTotalJoueurs,
            'nbMinJoueurs' => $nbMinJoueurs,
            'selected' => $selected,
            'compos' => $compos,
            'nbEquipes' => $nbEquipes,
            'selectedPlayers' => $selectedPlayers,
            'dispos' => $joueursDeclares,
            'disponible' => $disponible,
            'joueursNonDeclares' => $joueursNonDeclares,
            'dispoJoueur' => $dispoJoueur,
            'nbDispos' => $nbDispos,
            'nbJournees' => $nbJournees,
            'brulages' => $brulages,
            'allDisponibilitesDepartementales' => $allDisponibilitesDepartementales,
            'allDisponibiliteParis' => $allDisponibiliteParis
        ]);
    }

    /**
     * @Route("/composition/{type}/edit/{compo}", name="composition.edit")
     * @param string $type
     * @param $compo
     * @param Request $request
     * @param InvalidSelectionController $invalidSelectionController
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function edit(string $type, $compo, Request $request, InvalidSelectionController $invalidSelectionController) : Response
    {
        $nbEquipes = null;
        $joueursBrules = $futursSelectionnes = [];

        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($compo))) throw $this->createNotFoundException('Journée inexistante');

            $selectionnables = $this->disponibiliteDepartementaleRepository->findJoueursSelectionnables($compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getIdEquipe());
            $brulesJ2 = $this->rencontreDepartementaleRepository->getBrulesJ2($compo->getIdEquipe());
            $nbJoueursDivision = $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampDepartementale();
            $form = $this->createForm(RencontreDepartementaleType::class, $compo, [
                'nbMaxJoueurs' => $this->divisionRepository->getMaxNbJoueursChamp($type),
                'limiteBrulage' => $this->getParameter('limite_brulage_dep')
            ]);
            $journees = $this->journeeDepartementaleRepository->findAll();

            try { $nbEquipes = $this->equipeDepartementalRepository->getNbEquipesDepartementales(); }
            catch (NoResultException | NonUniqueResultException $e) { $nbEquipes = 0; }

            $brulages = $this->competiteurRepository->getBrulagesDepartemental($compo->getIdJournee()->getIdJournee());
            /** Formation de la liste des joueurs brûlés et pré-brûlés en championnat départemental **/
            foreach ($brulages as $joueur => $brulage){
                switch ($compo->getIdEquipe()->getIdEquipe()){
                    case 1:
                        if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                            $futursSelectionnes[$joueur]["E1"] = intval($futursSelectionnes[$joueur]["E1"]);
                            $futursSelectionnes[$joueur]["E1"]++;
                        }
                        break;
                    case 2:
                        if ($brulage["E1"] >= 2) array_push($joueursBrules, $joueur);
                        else if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                            $futursSelectionnes[$joueur]["E2"] = intval($futursSelectionnes[$joueur]["E2"]);
                            $futursSelectionnes[$joueur]["E2"]++;
                        }
                        break;
                    case 3:
                        if (($brulage["E1"] + $brulage["E2"]) >= 2) array_push($joueursBrules, $joueur);
                        else if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                            $futursSelectionnes[$joueur]["E3"] = intval($futursSelectionnes[$joueur]["E3"]);
                            $futursSelectionnes[$joueur]["E3"]++;
                        }
                        break;
                    case 4:
                        if (($brulage["E1"] + $brulage["E2"] + $brulage["E3"]) >= 2) array_push($joueursBrules, $joueur);
                        else if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                        }
                        break;
                }
            }
        }
        else if ($type == 'paris'){
            if (!($compo = $this->rencontreParisRepository->find($compo))) throw $this->createNotFoundException('Journée inexistante');

            $selectionnables = $this->disponibiliteParisRepository->findJoueursSelectionnables($compo->getIdJournee()->getIdJournee(), $compo->getIdEquipe()->getIdEquipe());
            $brulesJ2 = $this->rencontreParisRepository->getBrulesJ2($compo->getIdEquipe());
            $nbJoueursDivision = ($compo->getIdEquipe()->getIdDivision() ? $compo->getIdEquipe()->getIdDivision()->getNbJoueursChampParis() : $this->divisionRepository->getMaxNbJoueursChamp($type));
            $form = $this->createForm(RencontreParisType::class, $compo, [
                'nbMaxJoueurs' => $this->divisionRepository->getMaxNbJoueursChamp($type),
                'limiteBrulage' => $this->getParameter('limite_brulage_paris')
            ]);
            $journees = $this->journeeParisRepository->findAll();

            try { $nbEquipes = $this->equipeParisRepository->getNbEquipesParis(); }
            catch (NoResultException | NonUniqueResultException $e) { $nbEquipes = 0; }

            $brulages = $this->competiteurRepository->getBrulagesParis($compo->getIdJournee()->getIdJournee());
            /** Formation de la liste des joueurs brûlés et pré-brûlés en championnat de Paris **/
            foreach ($brulages as $joueur => $brulage){
                switch ($compo->getIdEquipe()->getIdEquipe()){
                    case 1:
                        if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                            $futursSelectionnes[$joueur]["E1"] = intval($futursSelectionnes[$joueur]["E1"]);
                            $futursSelectionnes[$joueur]["E1"]++;
                        }
                        break;
                    case 2:
                        if ($brulage["E1"] >= 3) array_push($joueursBrules, $joueur);
                        else if (in_array($joueur, $selectionnables)) {
                            $futursSelectionnes[$joueur] = $brulage;
                            $futursSelectionnes[$joueur]["idCompetiteur"] = $brulage["idCompetiteur"];
                        }
                        break;
                }
            }
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        foreach ($futursSelectionnes as $joueur => $fields){
            if ($compo->getIdJournee()->getIdJournee() == 2 && $compo->getIdEquipe()->getIdEquipe() > 1) $futursSelectionnes[$joueur]["bruleJ2"] = (in_array($fields["idCompetiteur"], $brulesJ2) ? true : false);
            else $futursSelectionnes[$joueur]["bruleJ2"] = false;
        }

        $form->handleRequest($request);

        $joueursBrulesRegleJ2 = array_column(array_filter($futursSelectionnes, function($joueur)
            {   return ($joueur["bruleJ2"]);    }
        ), 'idCompetiteur');

        if ($form->isSubmitted() && $form->isValid()) {
            /** On vérifie qu'il n'y aie pas 2 joueurs brûlés pour la règle de la J2 **/
            $nbJoueursBruleJ2 = 0;
            for ($i = 0; $i < $nbJoueursDivision; $i++) {
                if ($form->getData()->getIdJoueurN($i) && in_array($form->getData()->getIdJoueurN($i)->getIdCompetiteur(), $joueursBrulesRegleJ2)) $nbJoueursBruleJ2++;
            }

            if ($nbJoueursBruleJ2 >= 2) $this->addFlash('fail', $nbJoueursBruleJ2 . ' joueurs brûlés sont sélectionnés (règle de la J2 en rouge)');
            else {
                /** On vérifie si le joueur devient brûlé dans de futures compositions **/
                for ($i = 0; $i < $nbJoueursDivision; $i++) {
                    $invalidSelectionController->checkInvalidSelection($type, $compo, $form->getData()->getIdJoueurN($i));
                }
                $this->em->flush();
                $this->addFlash('success', 'Composition modifiée avec succès !');

                return $this->redirectToRoute('journee.show', [
                    'type' => $compo->getIdJournee()->getLinkType(),
                    'id' => $compo->getIdJournee()->getIdJournee()
                ]);
            }
        }

        return $this->render('journee/edit.html.twig', [
            'joueursBrules' => $joueursBrules,
            'futursSelectionnes' => $futursSelectionnes,
            'journees' => $journees,
            'nbJoueursDivision' => $nbJoueursDivision,
            'brulages' => $brulages,
            'nbEquipes' => $nbEquipes,
            'compo' => $compo,
            'type' => $type,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/composition/empty/{type}/{idRencontre}/{fromTemplate}/{nbJoueurs}", name="composition.vider")
     * @param string $type
     * @param int $idRencontre
     * @param bool $fromTemplate // Affiche le flash uniquement s'il est activé depuis le template journee/index.html.twig
     * @param int $nbJoueurs
     * @return Response
     */
    public function emptyComposition(string $type, int $idRencontre, bool $fromTemplate, int $nbJoueurs) : Response
    {
        $compo = null;
        if ($type == 'departementale'){
            if (!($compo = $this->rencontreDepartementaleRepository->find($idRencontre))) throw $this->createNotFoundException('Rencontre inexistante');
        }
        else if ($type == 'paris'){
            if (!($compo = $this->rencontreParisRepository->find($idRencontre))) throw $this->createNotFoundException('Rencontre inexistante');
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        for ($i = 0; $i < $nbJoueurs; $i++){
            $compo->setIdJoueurN($i, null);
        }

        $this->em->flush();
        if ($fromTemplate) $this->addFlash('success', 'Composition vidée avec succès !');

        return $this->redirectToRoute('journee.show', [
            'type' => $compo->getIdJournee()->getLinkType(),
            'id' => $compo->getIdJournee()->getIdJournee()
        ]);
    }
}