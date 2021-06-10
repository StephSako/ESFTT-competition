<?php

namespace App\Controller;

use App\Form\CompetiteurType;
use App\Repository\ChampionnatRepository;
use App\Repository\JourneeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $em;
    private $journeeRepository;
    private $championnatRepository;

    /**
     * SecurityController constructor.
     * @param JourneeRepository $journeeRepository
     * @param ChampionnatRepository $championnatRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(JourneeRepository $journeeRepository,
                                ChampionnatRepository $championnatRepository,
                                EntityManagerInterface $em)
    {
        $this->journeeRepository = $journeeRepository;
        $this->em = $em;
        $this->championnatRepository = $championnatRepository;
    }

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils): Response
    {
        if ($this->getUser() != null){
            return $this->redirectToRoute('index');
        } else {
            return $this->render('account/login.html.twig', [
                'lastUsername' => $utils->getLastUsername(),
                'error' => $utils->getLastAuthenticationError()
            ]);
        }
    }

    /**
     * @Route("/compte", name="account")
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function edit(Request $request){
        if (!$this->get('session')->get('type')) $championnat = $this->championnatRepository->getFirstChampionnatAvailable();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $this->championnatRepository->getFirstChampionnatAvailable());
        $journees = ($championnat ? $this->journeeRepository->findAllDates($championnat->getIdChampionnat()) : []);

        $allChampionnats = $this->championnatRepository->findAll();
        $user = $this->getUser();

        $form = $this->createForm(CompetiteurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()){
                try {
                    $user->setNom(mb_convert_case($user->getNom(), MB_CASE_UPPER, "UTF-8"));
                    $user->setPrenom(mb_convert_case($user->getPrenom(), MB_CASE_TITLE, "UTF-8"));
                    $this->em->flush();
                    $this->addFlash('success', 'Informations modifiées !');
                    return $this->redirectToRoute('account');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getPrevious()->getMessage(), 'username')) $this->addFlash('fail', 'Le pseudo \'' . $user->getUsername() . '\' est déjà attribué');
                        else if (str_contains($e->getPrevious()->getMessage(), 'CHK_mail')) $this->addFlash('fail', 'Les deux adresses emails doivent être différentes');
                        else if (str_contains($e->getPrevious()->getMessage(), 'CHK_phone_number')) $this->addFlash('fail', 'Les deux numéros de téléphone doivent être différents');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    }
                    else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    return $this->render('account/edit.html.twig', [
                        'type' => 'general',
                        'user' => $user,
                        'urlImage' => $user->getAvatar(),
                        'path' => 'account.update.password',
                        'allChampionnats' => $allChampionnats,
                        'championnat' => $championnat,
                        'journees' => $journees,
                        'form' => $form->createView()
                    ]);
                }
            }
            else {
                $this->addFlash('fail', 'Le formulaire n\'est pas valide');
            }
        }

        return $this->render('account/edit.html.twig', [
            'type' => 'general',
            'user' => $user,
            'urlImage' => $user->getAvatar(),
            'path' => 'account.update.password',
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'journees' => $journees,
            'form' => $form->createView()
        ]);
    }

    /**
     * // TODO Refaire
     * @Route("/compte/update_password", name="account.update.password")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        //TODO Classer selon le championnat en cache
        if (!$this->get('session')->get('type')) $championnat = $this->championnatRepository->getFirstChampionnatAvailable();
        else $championnat = ($this->championnatRepository->find($this->get('session')->get('type')) ?: $this->championnatRepository->getFirstChampionnatAvailable());
        $journees = ($championnat ? $this->journeeRepository->findAllDates($championnat->getIdChampionnat()) : []);
        $user = $this->getUser();

        $formCompetiteur = $this->createForm(CompetiteurType::class, $user);
        $formCompetiteur->handleRequest($request);

        if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
            $password = $encoder->encodePassword($user, $request->get('new_password'));
            $user->setPassword($password);

            $this->em->flush();
            $this->addFlash('success', 'Mot de passe modifié !');
        }
        else {
            $this->addFlash('fail', 'Les mots de passe ne correspond pas');
        }

        return $this->render('account/edit.html.twig', [
            'user' => $user,
            'type' => 'general',
            'urlImage' => $user->getAvatar(),
            'path' => 'account.update.password',
            'journees' => $journees,
            'form' => $formCompetiteur->createView()
        ]);
    }
}
