<?php

namespace App\Controller;

use App\Form\ClientsType;
use Symfony\Component\Mime\Email;
use App\Repository\Main\UsersRepository;
use App\Repository\Main\MailListRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\Divalto\ClientLhermitteByCommercialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_RESPONSABLE_SECTEUR")
 */

class ClientsParSecteurController extends AbstractController
{

    private $repoMail;
    private $mailEnvoi;
    private $mailTreatement;

    public function __construct(MailListRepository $repoMail)
    {
        $this->repoMail =$repoMail;
        $this->mailEnvoi = $this->repoMail->getEmailEnvoi()['email'];
        $this->mailTreatement = $this->repoMail->getEmailTreatement()['email'];
        //parent::__construct();
    }

    /**
     * @Route("/Lhermitte/clients", name="app_lhermitte_clients_secteur")
     */
    public function index(Request $request, ClientLhermitteByCommercialRepository $clients): Response
    {
                
        // tracking user page for stats
        $tracking = $request->attributes->get('_route');
        $this->setTracking($tracking);
        
            $clients = $clients->getClientLhermitteByCommercial();
            
        return $this->render('clients_par_secteur/index.html.twig', [
            'title' => 'Clients',
            'clients' => $clients,
        ]);
    }

    /**
     * @Route("/Lhermitte/contacts/client/{tiers}", name="app_lhermitte_contact_client")
     */
    public function getContactsClient($tiers, Request $request, ClientLhermitteByCommercialRepository $client): Response
    {
                
        // tracking user page for stats
        $tracking = $request->attributes->get('_route');
        $this->setTracking($tracking);
        
        return $this->render('clients_par_secteur/contact.html.twig', [
            'title' => 'Contacts Client',
            'client' => $client->getThisClient($tiers),
            'contacts' => $client->getContactsClient($tiers)
        ]);
    }

    /**
     * @Route("/Lhermitte/clients/need/{tiers}", name="app_lhermitte_need_clients")
     * 
     */
    public function need($tiers=null, Request $request, MailerInterface $mailer,ClientLhermitteByCommercialRepository $clients): Response
    {
                
        if ($tiers) {
            $mail = $clients->getClient($tiers);
            $nom = $mail['Nom'];
            if ($mail) {
                $mail = trim($mail['Email']);
            }
            if ($mail == '') {
                $mail =$this->mailTreatement;
            }

            $email = (new Email())
                        ->from($this->mailEnvoi)
                        ->to($this->getUser()->getEmail(),$mail)
                        ->subject('J\'aimerai récupérer ce client ' . $tiers)
                        ->html('Bonjour, </br> j\'aimerai suivre le client ' . $tiers . $nom );
                    $mailer->send($email);
            // tracking user page for stats
            $tracking = $request->attributes->get('_route');
            $this->setTracking($tracking);
                
            $this->addFlash('message', 'Demande envoyé avec succès');
            return $this->redirectToRoute('app_lhermitte_clients_secteur');
            }else
            {
                $this->addFlash('danger', 'Pas de code Tiers ?? WTF ! ');
            return $this->redirectToRoute('app_lhermitte_clients_secteur');
            }        
    }

    /**
     * @Route("/Lhermitte/clients/close/{tiers}", name="app_lhermitte_close_clients")
     * 
     */
    public function close($tiers=null, Request $request, MailerInterface $mailer): Response
    {
                
        
        if ($tiers) {

            $email = (new Email())
                        ->from($this->mailEnvoi)
                        ->to($this->mailTreatement)
                        ->subject('Merci de fermer ce client ' . $tiers)
                        ->html('Bonjour, </br> Merci de fermer le client ' . $tiers );
                    $mailer->send($email);
            // tracking user page for stats
            $tracking = $request->attributes->get('_route');
            $this->setTracking($tracking);
                
            $this->addFlash('message', 'Demande envoyé avec succès');
            return $this->redirectToRoute('app_lhermitte_clients_secteur');
            }else
            {
                $this->addFlash('danger', 'Pas de code Tiers ?? WTF ! ');
            return $this->redirectToRoute('app_lhermitte_clients_secteur');
            }        
    }

}
