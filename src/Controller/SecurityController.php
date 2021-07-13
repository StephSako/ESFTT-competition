<?php

namespace App\Controller;

use App\Form\CompetiteurType;
use App\Repository\ChampionnatRepository;
use App\Repository\CompetiteurRepository;
use App\Repository\JourneeRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Vich\UploaderBundle\Handler\UploadHandler;

class SecurityController extends AbstractController
{
    private $em;
    private $journeeRepository;
    private $championnatRepository;
    private $utils;
    private $uploadHandler;
    private $encoder;
    private $competiteurRepository;

    /**
     * SecurityController constructor.
     * @param JourneeRepository $journeeRepository
     * @param CompetiteurRepository $competiteurRepository
     * @param ChampionnatRepository $championnatRepository
     * @param EntityManagerInterface $em
     * @param AuthenticationUtils $utils
     * @param UploadHandler $uploadHandler
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(JourneeRepository $journeeRepository,
                                CompetiteurRepository $competiteurRepository,
                                ChampionnatRepository $championnatRepository,
                                EntityManagerInterface $em,
                                AuthenticationUtils $utils,
                                UploadHandler $uploadHandler,
                                UserPasswordEncoderInterface $encoder)
    {
        $this->journeeRepository = $journeeRepository;
        $this->em = $em;
        $this->championnatRepository = $championnatRepository;
        $this->utils = $utils;
        $this->uploadHandler = $uploadHandler;
        $this->encoder = $encoder;
        $this->competiteurRepository = $competiteurRepository;
    }

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils): Response
    {
        if ($this->getUser() != null) return $this->redirectToRoute('index');
        else {
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
                    $user->setNom($user->getNom());
                    $user->setPrenom($user->getPrenom());

                    $this->em->flush();
                    $this->addFlash('success', 'Informations modifiées');
                    return $this->redirectToRoute('account');
                } catch(Exception $e){
                    if ($e->getPrevious()->getCode() == "23000"){
                        if (str_contains($e->getPrevious()->getMessage(), 'username')) $this->addFlash('fail', 'Le pseudo \'' . $user->getUsername() . '\' est déjà attribué');
                        else if (str_contains($e->getPrevious()->getMessage(), 'CHK_mail_mandatory')) $this->addFlash('fail', 'Au moins une adresse email doit être renseignée');
                        else if (str_contains($e->getPrevious()->getMessage(), 'CHK_mail')) $this->addFlash('fail', 'Les deux adresses email doivent être différentes');
                        else if (str_contains($e->getPrevious()->getMessage(), 'CHK_phone_number')) $this->addFlash('fail', 'Les deux numéros de téléphone doivent être différents');
                        else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                    } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
                }
            } else $this->addFlash('fail', 'Le formulaire n\'est pas valide');
        }

        return $this->render('account/edit.html.twig', [
            'type' => 'general',
            'urlImage' => $user->getAvatar(),
            'anneeCertificatMedical' => $user->getAnneeCertificatMedical(),
            'path' => 'account.update.password',
            'allChampionnats' => $allChampionnats,
            'championnat' => $championnat,
            'journees' => $journees,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/compte/update_password", name="account.update.password")
     * @param Request $request
     * @return Response
     */
    public function updatePassword(Request $request): Response
    {
        $user = $this->getUser();
        $formCompetiteur = $this->createForm(CompetiteurType::class, $user);
        $formCompetiteur->handleRequest($request);

        if (strlen($request->request->get('new_password')) && strlen($request->request->get('new_password_validate')) && strlen($request->request->get('actual_password'))) {
            if ($this->encoder->isPasswordValid($user, $request->request->get('actual_password'))) {
                if ($request->request->get('new_password') == $request->request->get('new_password_validate')) {
                    $user->setPassword($this->encoder->encodePassword($user, $request->get('new_password')));
                    $this->em->flush();
                    $this->addFlash('success', 'Mot de passe modifié');
                } else $this->addFlash('fail', 'Champs du nouveau mot de passe différents');
            } else $this->addFlash('fail', 'Mot de passe actuel incorrect');
        } else $this->addFlash('fail', 'Remplissez tous les champs');

        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/compte/delete/avatar", name="account.delete.avatar")
     * @return Response
     */
    public function deleteAvatar(): Response
    {
        if ($this->getUser() != null){
            $this->uploadHandler->remove($this->getUser(), 'imageFile');
            $this->getUser()->setAvatar(null);
            $this->getUser()->setImageFile(null);

            $this->em->flush();
            $this->addFlash('success', 'Avatar supprimé');
        } else {
            return $this->render('account/login.html.twig', [
                'lastUsername' => $this->utils->getLastUsername(),
                'error' => $this->utils->getLastAuthenticationError()
            ]);
        }
        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/login/forgotten_password", name="login.forgotten.password")
     * @return Response
     */
    public function forgottenPassword(): Response
    {
        if ($this->getUser() != null) return $this->redirectToRoute('index');
        else return $this->render('account/forgotten_password.html.twig', []);
    }

    /**
     * @Route("/login/reset_password/{token}", name="login.reset.password")
     * @param Request $request
     * @param string $token
     * @return Response
     * @throws Exception
     */
    public function resetPassword(Request $request, string $token): Response
    {
        if ($this->getUser() != null) return $this->redirectToRoute('index');
        else {
            $tokenDecoded = base64_decode($token);
            $decryption_key = openssl_digest(php_uname(), 'MD5', TRUE);
            $decryption = openssl_decrypt($tokenDecoded, "BF-CBC", $decryption_key, 0, hex2bin($this->getParameter('encryption_iv')));

            $username = json_decode($decryption, true)['username'];
            $dateValid = json_decode($decryption, true)['dateValidation'];

            /** On vérifie que le lien de réinitialisation du mot de passe soit toujours actif **/
            if ($dateValid < (new DateTime())->getTimestamp()) throw new Exception('Ce lien n\'est plus actif', 500);

            /**Formulaire soumis **/
            if ($request->request->get('new_password') && $request->request->get('new_password_validate')) {
                if ($request->request->get('new_password') == $request->request->get('new_password_validate')){
                    $competiteur = $this->competiteurRepository->findBy(['username' => $username])[0];
                    $competiteur->setPassword($this->encoder->encodePassword($competiteur, $request->get('new_password_validate')));
                    $this->em->flush();
                    $this->addFlash('success', 'Mot de passe modifié');
                    return $this->redirectToRoute('login');
                } else $this->addFlash('fail', 'Champs du nouveau mot de passe différents');
            }

            return $this->render('account/reset_password.html.twig', [
                'token' => $token
            ]);
        }
    }
}
