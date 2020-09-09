<?php

namespace App\Controller;

use App\Form\RencontreDepartementaleType;
use App\Form\RencontreParisBasType;
use App\Form\RencontreParisHautType;
use App\Repository\CompetiteurRepository;
use App\Repository\DisponibiliteDepartementaleRepository;
use App\Repository\DisponibiliteParisRepository;
use App\Repository\JourneeParisRepository;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\JourneeDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use FFTTApi\Exception\InvalidURIParametersException;
use FFTTApi\Exception\JoueurNotFound;
use FFTTApi\Exception\NoFFTTResponseException;
use FFTTApi\Exception\URIPartNotValidException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FFTTApi\FFTTApi;

/**
 * Class Rencontre_1Controller
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    private $em;
    private $competiteurRepository;
    private $disponibiliteDepartementaleRepository;
    private $disponibiliteParisRepository;
    private $journeeDepartementaleRepository;
    private $journeeParisRepository;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;

    /**
     * @param JourneeDepartementaleRepository $journeeDepartementaleRepository
     * @param JourneeParisRepository $journeeParisRepository
     * @param DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository
     * @param DisponibiliteParisRepository $disponibiliteParisRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeDepartementaleRepository $journeeDepartementaleRepository,
                                JourneeParisRepository $journeeParisRepository,
                                DisponibiliteDepartementaleRepository $disponibiliteDepartementaleRepository,
                                DisponibiliteParisRepository $disponibiliteParisRepository,
                                CompetiteurRepository $competiteurRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                RencontreParisRepository $rencontreParisRepository,
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
    }

    /**
     * @Route("/test_api", name="test_api")
     */
    public function testApiFFTTAction(){
        $api = new FFTTApi("SW405", "d7ZG56dQKf");
        try {
            //var_dump($api->getEquipesByClub('08951331'));
        } catch (InvalidURIParametersException $e) {//var_dump($e->getMessage());
        } catch (NoFFTTResponseException $e) {//var_dump($e->getMessage());
        } catch (URIPartNotValidException $e) {//var_dump($e->getMessage());
        } catch (JoueurNotFound $e) {//var_dump($e->getMessage());
        }

        return new Response('');
    }

    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        $dates = [];
        $type = ($this->get('session')->get('type') ?: 'departementale');
        if ($type == 'departementale') $dates = $this->journeeDepartementaleRepository->findAllDates();
        else if ($type == 'paris') $dates = $this->journeeParisRepository->findAllDates();
        $NJournee = 0;

        while ($NJournee < 7 && (int) (new DateTime())->diff($dates[$NJournee]["date"])->format('%R%a') <= 0){
            $NJournee++;
        }
        $NJournee++;

        return $this->redirectToRoute('journee.show', [
            'type' => $type,
            'id' => $NJournee
        ]);
    }

    /**
     * @param $type
     * @param $id
     * @return Response
     * @throws DBALException
     * @Route("/journee/{type}/{id}", name="journee.show")
     */
    public function journeeShow($type, $id)
    {
        $this->get('session')->set('type', $type);
        $journee = $compos = $selectedPlayers = $joueursDeclares = $joueursNonDeclares = $journees = null;
        $disposJoueur = [];

        $competiteurs = $this->competiteurRepository->findBy([], ['nom' => 'ASC']);

        if ($this->get('session')->get('type') === 'departementale'){
            $disposJoueur = $this->getUser() ? $this->disponibiliteDepartementaleRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
            $journee = $this->journeeDepartementaleRepository->find($id);
            $joueursDeclares = $this->disponibiliteDepartementaleRepository->findAllDisposByJournee($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, 'disponibilite_departementale');
            $compos = $this->rencontreDepartementaleRepository->findBy(['idJournee' => $id]);
            $selectedPlayers = $this->rencontreDepartementaleRepository->getSelectedPlayers($compos);
            $journees = $this->journeeDepartementaleRepository->findAll();
        } else if ($this->get('session')->get('type') === 'paris'){
            $disposJoueur = $this->getUser() ? $this->disponibiliteParisRepository->findOneBy(['idCompetiteur' => $this->getUser()->getIdCompetiteur(), 'idJournee' => $id]) : null;
            $journee = $this->journeeParisRepository->find($id);
            $joueursDeclares = $this->disponibiliteParisRepository->findAllDisposByJournee($id);
            $joueursNonDeclares = $this->competiteurRepository->findJoueursNonDeclares($id, 'disponibilite_paris');
            $compos = $this->rencontreParisRepository->findBy(['idJournee' => $id]);
            $selectedPlayers = $this->rencontreParisRepository->getSelectedPlayers($compos);
            $journees = $this->journeeParisRepository->findAll();
        }

        $classement = array("1"=>[], "2"=>[], "3"=>[], "4"=>[]);

        return $this->render('journee/index.html.twig', [
            'journee' => $journee,
            'journees' => $journees,
            'compos' => $compos,
            'selectedPlayers' => $selectedPlayers,
            'dispos' => $joueursDeclares,
            'joueursNonDeclares' => $joueursNonDeclares,
            'disposJoueur' => $disposJoueur,
            'competiteurs' => $competiteurs,
            'classement' => $classement,
            'allDisponibilitesDepartementales' => $this->competiteurRepository->findAllDisposRecapitulatif("departementale"),
            'allDisponibiliteParis' => $this->competiteurRepository->findAllDisposRecapitulatif("paris")
        ]);
    }

    /**
     * @Route("/composition/{type}/edit/{compo}", name="composition.edit")
     * @param string $type
     * @param $compo
     * @param Request $request
     * @return Response
     */
    public function edit(string $type, $compo, Request $request) : Response
    {
        $oldPlayers = $form = $j4 = $j5 = $j6 = $j7 = $j8 = $j9 = $levelEquipe = $burntPlayers = $selectionnables = $almostBurntPlayers = $selectedPlayers = $journees = null;

        if ($type == 'departementale'){
            $compo = $this->rencontreDepartementaleRepository->find($compo);
            $journeeSelectedPlayers = $this->rencontreDepartementaleRepository->findBy(['idJournee' => $compo->getIdJournee()->getIdJournee()]);
            $selectionnables = $this->disponibiliteDepartementaleRepository->findSelectionnablesDepartementales($compo->getIdEquipe(), $compo->getIdJournee()->getIdJournee());
            $form = $this->createForm(RencontreDepartementaleType::class, $compo);
            $oldPlayers = $this->rencontreDepartementaleRepository->findOneBy(['id' => $compo->getId()]);
            $j4 = $oldPlayers->getIdJoueur4();
            $selectedPlayers = $this->rencontreDepartementaleRepository->getSelectedPlayers($journeeSelectedPlayers);
            $journees = $this->journeeDepartementaleRepository->findAll();
        } else if ($type == 'paris'){
            $compo = $this->rencontreParisRepository->find($compo);
            $journeeSelectedPlayers = $this->rencontreParisRepository->findBy(['idJournee' => $compo->getIdJournee()->getIdJournee()]);
            $selectionnables = $this->disponibiliteParisRepository->findSelectionnablesParis($compo->getIdEquipe(), $compo->getIdJournee()->getIdJournee());
            $levelEquipe = $compo->getIdEquipe()->getIdEquipe();
            $oldPlayers = $this->rencontreParisRepository->findOneBy(['id' => $compo->getId()]);
            if ($levelEquipe === 1){
                $form = $this->createForm(RencontreParisHautType::class, $compo);
                $j4 = $oldPlayers->getIdJoueur4();
                $j5 = $oldPlayers->getIdJoueur5();
                $j6 = $oldPlayers->getIdJoueur6();
                $j7 = $oldPlayers->getIdJoueur7();
                $j8 = $oldPlayers->getIdJoueur8();
                $j9 = $oldPlayers->getIdJoueur9();
            } else if ($levelEquipe === 2){
                $form = $this->createForm(RencontreParisBasType::class, $compo);
            }
            $selectedPlayers = $this->rencontreParisRepository->getSelectedPlayers($journeeSelectedPlayers);
            $journees = $this->journeeParisRepository->findAll();
        }

        $j1 = $oldPlayers->getIdJoueur1();
        $j2 = $oldPlayers->getIdJoueur2();
        $j3 = $oldPlayers->getIdJoueur3();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** Décrémenter le brûlage des joueurs désélectionnés de la précédente compo **/
            $this->decrementeBrulage1234($j1, $type, $compo);
            $this->decrementeBrulage1234($j2, $type, $compo);
            $this->decrementeBrulage1234($j3, $type, $compo);

            if ($type === 'departementale' || ($type === 'paris' && $levelEquipe === 1)) {
                $this->decrementeBrulage1234($j4, $type, $compo);
            }

            if ($type === 'paris' && $levelEquipe === 1) {
                $this->decrementeBrulage56789($j5, $compo);
                $this->decrementeBrulage56789($j6, $compo);
                $this->decrementeBrulage56789($j7, $compo);
                $this->decrementeBrulage56789($j8, $compo);
                $this->decrementeBrulage56789($j9, $compo);
            }

            /** Incrémenter le brûlage des joueurs sélectionnés de la nouvelle compo **/
            $this->incrementeBrulage1234($type, $compo, $form->getData()->getIdJoueur1(), $compo->getIdJoueur1());
            $this->incrementeBrulage1234($type, $compo, $form->getData()->getIdJoueur2(), $compo->getIdJoueur2());
            $this->incrementeBrulage1234($type, $compo, $form->getData()->getIdJoueur3(), $compo->getIdJoueur3());

            if ($type === 'departementale' || ($type === 'paris' && $levelEquipe === 1)) {
                $this->incrementeBrulage1234($type, $compo, $form->getData()->getIdJoueur4(), $compo->getIdJoueur4());
            }

            if ($type === 'paris' && $levelEquipe === 1) {
                $this->incrementeBrulage56789($compo, $form->getData()->getIdJoueur5(), $compo->getIdJoueur5());
                $this->incrementeBrulage56789($compo, $form->getData()->getIdJoueur6(), $compo->getIdJoueur6());
                $this->incrementeBrulage56789($compo, $form->getData()->getIdJoueur7(), $compo->getIdJoueur7());
                $this->incrementeBrulage56789($compo, $form->getData()->getIdJoueur8(), $compo->getIdJoueur8());
                $this->incrementeBrulage56789($compo, $form->getData()->getIdJoueur9(), $compo->getIdJoueur9());
            }

            $this->em->flush();
            $this->addFlash('success', 'Composition modifiée avec succès !');

            return $this->redirectToRoute('journee.show', [
                'type' => $compo->getIdJournee()->getLinkType(),
                'id' => $compo->getIdJournee()->getIdJournee()
            ]);
        }

        if ($type === 'departementale'){
            $burntPlayers = $this->competiteurRepository->findBurnPlayersDepartementale($compo->getIdEquipe());
            $almostBurntPlayers = $this->competiteurRepository->findAlmostBurnPlayersDepartementale($compo->getIdEquipe());
        } else if ($type === 'paris'){
            $burntPlayers = $this->competiteurRepository->findBurnPlayersParis();
            $almostBurntPlayers = $this->competiteurRepository->findAlmostBurnPlayersParis();
        }

        return $this->render('journee/edit.html.twig', [
            'burntPlayers' => $burntPlayers,
            'selectionnables' => $selectionnables,
            'almostBurntPlayers' => $almostBurntPlayers,
            'selectedPlayers' => $selectedPlayers,
            'journees' => $journees,
            'compo' => $compo,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $j
     * @param $type
     * @param $compo
     */
    public function decrementeBrulage1234($j, $type, $compo){
        if ($j != null) {
            if ($type === 'departementale') {
                $brulageOld2 = $j->getBrulageDepartemental();
                $brulageOld2[$compo->getIdEquipe()->getIdEquipe()]--;
                $j->setBrulageDepartemental($brulageOld2);
            } else if ($type === 'paris') {
                $brulageOld2 = $j->getBrulageParis();
                $brulageOld2[$compo->getIdEquipe()->getIdEquipe()]--;
                $j->setBrulageParis($brulageOld2);
            }
        }
    }

    /**
     * @param $j
     * @param $compo
     */
    public function decrementeBrulage56789($j, $compo){
        if ($j != null) {
            $brulageOld5 = $j->getBrulageParis();
            $brulageOld5[$compo->getIdEquipe()->getIdEquipe()]--;
            $j->setBrulageParis($brulageOld5);
        }
    }

    /**
     * @param $type
     * @param $compo
     * @param $jForm
     * @param $jCompo
     */
    public function incrementeBrulage1234($type, $compo, $jForm, $jCompo){
        if ($jForm != null) {
            if ($type === 'departementale') {
                $brulage1 = $jForm->getBrulageDepartemental();
                $brulage1[$compo->getIdEquipe()->getIdEquipe()]++;
                $jCompo->setBrulageDepartemental($brulage1);
            } else if ($type === 'paris') {
                $brulage1 = $jForm->getBrulageParis();
                $brulage1[$compo->getIdEquipe()->getIdEquipe()]++;
                $jCompo->setBrulageParis($brulage1);
            }
        }
    }

    /**
     * @param $compo
     * @param $jForm
     * @param $jCompo
     */
    public function incrementeBrulage56789($compo, $jForm, $jCompo){
        if ($jForm != null) {
            $brulage5 = $jForm->getBrulageParis();
            $brulage5[$compo->getIdEquipe()->getIdEquipe()]++;
            $jCompo->setBrulageParis($brulage5);
        }
    }
}
