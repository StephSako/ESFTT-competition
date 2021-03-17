<?php

namespace App\Controller\BackOffice;

use App\Form\EquipeDepartementaleType;
use App\Form\EquipeParisType;
use App\Repository\EquipeDepartementaleRepository;
use App\Repository\EquipeParisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackOfficeEquipeController extends AbstractController
{
    private $em;
    private $equipeDepartementaleRepository;
    private $equipeParisRepository;

    /**
     * BackOfficeController constructor.
     * @param EquipeDepartementaleRepository $equipeDepartementaleRepository
     * @param EquipeParisRepository $equipeParisRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(EquipeDepartementaleRepository $equipeDepartementaleRepository,
                                EquipeParisRepository $equipeParisRepository,
                                EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->equipeDepartementaleRepository = $equipeDepartementaleRepository;
        $this->equipeParisRepository = $equipeParisRepository;
    }

    /**
     * @Route("/backoffice/equipes", name="back_office.equipes")
     * @return Response
     */
    public function indexEquipes(): Response
    {
        return $this->render('back_office/equipes/index.html.twig', [
            'equipesDepartementales' => $this->equipeDepartementaleRepository->findAll(),
            'equipesParis' => $this->equipeParisRepository->findAll(),
        ]);
    }

    /**
     * @Route("/backoffice/equipe/edit/{type}/{idEquipe}", name="backoffice.equipe.edit")
     * @param $idEquipe
     * @param $type
     * @param Request $request
     * @return Response
     */
    public function update($type, $idEquipe, Request $request): Response
    {
        $form = null;
        if ($type == 'departementale'){
            if (!($equipe = $this->equipeDepartementaleRepository->find($idEquipe))) throw $this->createNotFoundException('Equipe inexistante');
            $form = $this->createForm(EquipeDepartementaleType::class, $equipe);
        }
        else if ($type == 'paris'){
            if (!($equipe = $this->equipeParisRepository->find($idEquipe))) throw $this->createNotFoundException('Equipe inexistante');
            $form = $this->createForm(EquipeParisType::class, $equipe);
        }
        else throw $this->createNotFoundException('Championnat inexistant');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->em->flush();
            $this->addFlash('success', 'Equipe modifiée avec succès !');
            return $this->redirectToRoute('back_office.equipes');
        }

        return $this->render('back_office/equipes/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/backoffice/equipe/delete/{type}/{id}", name="backoffice.equipe.delete", methods="DELETE")
     * @param int $id
     * @param string $type
     * @param Request $request
     * @return Response
     */
    public function delete(int $id, string $type, Request $request): Response
    {
        if ($type == 'departementale') $equipe = $this->equipeDepartementaleRepository->find($id);
        else if ($type == 'paris') $equipe = $this->equipeParisRepository->find($id);
        else throw $this->createNotFoundException('Championnat inexistant');

        if ($this->isCsrfTokenValid('delete' . $equipe->getIdEquipe(), $request->get('_token'))) {
            $this->em->remove($equipe);
            $this->em->flush();
            $this->addFlash('success', 'Équipe supprimée avec succès !');
        } else $this->addFlash('error', 'Léquipe n\'a pas pu être supprimée');

        return $this->render('back_office/equipes/index.html.twig', [
            'equipesDepartementales' => $this->equipeDepartementaleRepository->findAll(),
            'equipesParis' => $this->equipeParisRepository->findAll(),
        ]);
    }
}
