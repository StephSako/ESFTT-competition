<?php

namespace App\Controller\BackOffice;

use App\Form\BackOfficeRencontreDepartementaleType;
use App\Form\BackOfficeRencontreParisType;
use App\Repository\RencontreDepartementaleRepository;
use App\Repository\RencontreParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeRencontreController extends AbstractController
{
    private $em;
    private $rencontreDepartementaleRepository;
    private $rencontreParisRepository;

    /**
     * BackOfficeController constructor.
     * @param RencontreDepartementaleRepository $rencontreDepartementaleRepository
     * @param RencontreParisRepository $rencontreParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(RencontreParisRepository $rencontreParisRepository,
                                RencontreDepartementaleRepository $rencontreDepartementaleRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->rencontreParisRepository = $rencontreParisRepository;
        $this->rencontreDepartementaleRepository = $rencontreDepartementaleRepository;
    }

    /**
     * @Route("/backoffice/rencontres", name="back_office.rencontres")
     * @return Response
     */
    public function indexRencontre()
    {
        return $this->render('back_office/rencontre/index.html.twig', [
            'rencontreDepartementales' => $this->rencontreDepartementaleRepository->getOrderedRencontres(),
            'rencontreParis' => $this->rencontreParisRepository->getOrderedRencontres()
        ]);
    }

    /**
     * @Route("/backoffice/rencontre/edit/{type}/{idRencontre}", name="backoffice.rencontre.edit")
     * @param $idRencontre
     * @param $type
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function editRencontre($type, $idRencontre, Request $request)
    {
        $domicile = null;
        if ($type == 'departementale'){
            if (!($rencontre = $this->rencontreDepartementaleRepository->find($idRencontre))) throw $this->createNotFoundException('Composition inexistante');
            $form = $this->createForm(BackOfficeRencontreDepartementaleType::class, $rencontre);
            $domicile = $rencontre->getDomicile();
        }
        else if ($type == 'paris'){
            if (!($rencontre = $this->rencontreParisRepository->find($idRencontre))) throw $this->createNotFoundException('Composition inexistante');
            $form = $this->createForm(BackOfficeRencontreParisType::class, $rencontre);
            $domicile = $rencontre->getDomicile();
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $rencontre->setDomicile(($request->get('lieu_rencontre') == 'on' ? 0 : 1 ));
            $this->em->flush();
            $this->addFlash('success', 'Recontre modifiée avec succès !');
            return $this->redirectToRoute('back_office.rencontres');
        }

        return $this->render('back_office/rencontre/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
            'domicile' => $domicile,
            'date' => $rencontre->getIdJournee()->getDate(),
            'idJournee' => $rencontre->getIdJournee()->getIdJournee(),
            'idEquipe' => $rencontre->getIdEquipe()->getIdEquipe()
        ]);
    }
}
