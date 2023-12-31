<?php

namespace App\Controller;

use App\Controller\AffairesAdminController;
use App\Entity\Main\AffairePiece;
use App\Entity\Main\Affaires;
use App\Entity\Main\Chats;
use App\Entity\Main\InterventionFicheMonteur;
use App\Entity\Main\InterventionFichesMonteursHeures;
use App\Entity\Main\InterventionMonteurs;
use App\Entity\Main\OthersDocuments;
use App\Form\AffaireType;
use App\Form\ChatsType;
use App\Form\CreationChantierAffaireType;
use App\Form\InterventionFicheCommentType;
use App\Form\InterventionFichesMonteursHeuresType;
use App\Form\InterventionFicheType;
use App\Form\InterventionsMonteursType;
use App\Form\OthersDocumentsMultipleType;
use App\Repository\Divalto\ArtRepository;
use App\Repository\Divalto\CliRepository;
use App\Repository\Divalto\MouvRepository;
use App\Repository\Main\AffairePieceRepository;
use App\Repository\Main\AffairesRepository;
use App\Repository\Main\ChatsRepository;
use App\Repository\Main\CommentairesRepository;
use App\Repository\Main\InterventionFicheMonteurRepository;
use App\Repository\Main\InterventionFichesMonteursHeuresRepository;
use App\Repository\Main\InterventionMonteursRepository;
use App\Repository\Main\OthersDocumentsRepository;
use App\Repository\Main\RetraitMarchandisesEanRepository;
use App\Repository\Main\UsersRepository;
use App\Service\EmailTreatementService;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_MONTEUR")]

class AffairesController extends AbstractController
{

    private $repoMouv;
    private $repoArt;
    private $repoAffaires;
    private $repoAffairePiece;
    private $repoUsers;
    private $repoChats;
    private $repoIntervertionsMonteurs;
    private $repoInterventionFicheMonteur;
    private $repoInterventionFichesMonteursHeures;
    private $affaireAdminController;
    private $repoFiche;
    private $repoRetrait;
    private $emailTreatementService;
    private $entityManager;

    public function __construct(
        ManagerRegistry $registry,
        EmailTreatementService $emailTreatementService,
        ArtRepository $repoArt,
        RetraitMarchandisesEanRepository $repoRetrait,
        InterventionFicheMonteurRepository $repoFiche,
        AffairesAdminController $affaireAdminController,
        InterventionFichesMonteursHeuresRepository $repoInterventionFichesMonteursHeures,
        InterventionFicheMonteurRepository $repoInterventionFicheMonteur,
        InterventionMonteursRepository $repoIntervertionsMonteurs,
        CliRepository $repoCli,
        UsersRepository $repoUsers,
        ChatsRepository $repoChats,
        AffairePieceRepository $repoAffairePiece,
        OthersDocumentsRepository $repoDocs,
        MailerInterface $mailer,
        CommentairesRepository $repoComments,
        AffairesRepository $repoAffaires,
        MouvRepository $repoMouv) {
        $this->repoMouv = $repoMouv;
        $this->repoIntervertionsMonteurs = $repoIntervertionsMonteurs;
        $this->repoCli = $repoCli;
        $this->repoAffaires = $repoAffaires;
        $this->repoComments = $repoComments;
        $this->repoDocs = $repoDocs;
        $this->mailer = $mailer;
        $this->repoAffairePiece = $repoAffairePiece;
        $this->repoChats = $repoChats;
        $this->repoUsers = $repoUsers;
        $this->repoInterventionFichesMonteursHeures = $repoInterventionFichesMonteursHeures;
        $this->repoInterventionFicheMonteur = $repoInterventionFicheMonteur;
        $this->affaireAdminController = $affaireAdminController;
        $this->repoFiche = $repoFiche;
        $this->repoRetrait = $repoRetrait;
        $this->repoArt = $repoArt;
        $this->emailTreatementService = $emailTreatementService;
        $this->entityManager = $registry->getManager();
        //parent::__construct();
    }

    #[Route("/Lhermitte/affaire/me/ok", name: "app_affaire_me_ok")]
    #[Route("/Lhermitte/affaire/me/nok", name: "app_affaire_me_nok")]

    public function affaire(UrlGeneratorInterface $urlGenerator, Request $request): Response
    {

        $tracking = $request->attributes->get('_route');
        //$this->setTracking($tracking);

        // Calendrier des congés
        $events = $this->repoIntervertionsMonteurs->findAll();
        $interventionsActuels = $this->repoIntervertionsMonteurs->findInterventionDuMoment();
        $rdvs = [];

        foreach ($events as $event) {
            $id = $event->getId();
            $code = $event->getCode()->getCode();
            $libelle = $event->getCode()->getLibelle();
            if ($event->getBackgroundColor()) {
                $color = $event->getBackgroundColor();
            }
            if ($event->getTextColor()) {
                $textColor = $event->getTextColor();
            }
            if ($event->getStart() && $event->getEnd()) {
                $start = $event->getStart()->format('Y-m-d H:i:s');
                $end = $event->getEnd()->format('Y-m-d H:i:s');
                if ($event->getStart()->format('Y-m-d') == $event->getEnd()->format('Y-m-d') && $event->getStart()->format('H:i') == '00:00' && $event->getEnd()->format('H:i') == '23:00') {
                    $start = $event->getStart()->format('Y-m-d');
                    $end = $event->getEnd()->format('Y-m-d');
                }
            }
            if ($event->getStart() && $event->getEnd()) {
                $rdvs[] = [
                    'id' => $event->getId(),
                    'start' => $start,
                    'end' => $end,
                    'url' => $urlGenerator->generate('app_affaire_show_intervention', ['id' => $id]),
                    'title' => 'Affaire : ' . $libelle . ' du ' . $event->getStart()->format('d-m-Y H:i') . ' au ' . $event->getEnd()->format('d-m-Y H:i'),
                    'backgroundColor' => $color,
                    'borderColor' => '#FFFFFF',
                    'textColor' => $textColor,
                ];
            }
        }

        // récupérer les fériers en JSON sur le site etalab
        $ferierJson = file_get_contents("https://etalab.github.io/jours-feries-france-data/json/metropole.json");
        // On ajoute les fériers au calendrier des congés
        $jsonIterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator(json_decode($ferierJson, true)),
            RecursiveIteratorIterator::SELF_FIRST);
        foreach ($jsonIterator as $key => $val) {
            $rdvs[] = [
                'id' => '',
                'start' => $key,
                'end' => $key,
                'title' => $val,
                'backgroundColor' => '#404040',
                'borderColor' => '#FFFFFF',
                'textColor' => '#FFFFFF',
            ];
        }

        $data = json_encode($rdvs);
        // création d'un nouveau chantier en Urgence
        $form = $this->createForm(CreationChantierAffaireType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $chantierManu = $this->repoAffaires->findOneBy(['code' => $data['code'] . 'Manuelle']);
            if ($chantierManu) {
                $this->addFlash('danger', 'Ce code chantier est déjà utilisé, veuillez en choisir un autre');
                return $this->redirectToRoute('app_affaire_me_nok');
            } else {
                //Création du chantier
                $chantier = new Affaires();
                $chantier->setCode($data['code'] . 'Manuelle')
                    ->setLibelle($data['libelle'])
                    ->setTiers($data['tiers'])
                    ->setNom($data['tiers']);
                $chantier->setStart(new DateTime())
                    ->setEtat('Nouvelle');
                $em = $this->entityManager;
                $em->persist($chantier);
                $em->flush();
                // Création de la piéce de ce chantier
                $pieceChantier = new AffairePiece();
                $pieceChantier->setEntId('999999')
                    ->setTypePiece('2')
                    ->setPiece('99999999')
                    ->setOp('C')
                    ->setEtat('Nouvelle')
                    ->setAffaire($data['code'] . 'Manuelle');
                if ($data['adresse']) {
                    $pieceChantier->setAdresse($data['adresse']);
                } else {
                    $pieceChantier->setAdresse($data['tiers']);
                }
                $em = $this->entityManager;
                $em->persist($pieceChantier);
                $em->flush();

                // création de l'intervention pour ce chantier
                $intervention = new InterventionMonteurs();
                $intervention->setCreatedAt(new DateTime())
                    ->setUserCr($this->getUser())
                    ->setStart($data['start'])
                    ->setEnd($data['end'])
                    ->addPiece($pieceChantier)
                    ->setBackgroundColor('#c40000')
                    ->setTextColor('#ffffff')
                    ->setCode($chantier);
                foreach ($data['Equipes'] as $monteur) {
                    $intervention->addEquipe($monteur);
                }
                if ($data['adresse']) {
                    $intervention->setAdresse($data['adresse']);
                } else {
                    $intervention->setAdresse($data['tiers']);
                }
            }
            $entityManager = $this->entityManager;
            $entityManager->persist($intervention);
            $entityManager->flush();
            // on ajoute le commentaire s'il y en a un
            if ($data['comment']) {
                $chat = new Chats;
                $chat->setCreatedAt(new \DateTime())
                    ->setUser($this->getUser())
                    ->setContent($data['comment'])
                    ->setFonction('chatAffaire')
                    ->setIdentifiant($intervention->getCode()->getId())
                    ->setTables($intervention->getId());
                $entityManager = $this->entityManager;
                $entityManager->persist($chat);
                $entityManager->flush();

            }

            $subjet = "Un chantier en Urgence à été déposé => " . $intervention->getCode()->getLibelle();
            $donnees = $intervention;
            if ($data['comment']) {
                $donnees->setChampTemporaire($data['comment']);
            }
            $emails = null;
            $equipes = $intervention->getEquipes();
            if ($equipes) {
                $equipesArray = $equipes->toArray(); // Convertir la collection en un tableau
                $emails = array_map(function ($equipe) {
                    return $equipe->getEmail();
                }, $equipesArray);
            }
            $pageUrl = "affaires/mails/mailChantierUrgence.html.twig";
            $mails = $this->emailTreatementService->treatementMails('app_affaires_admin', null, $emails);
            $urlFiles = [];
            $this->emailTreatementService->sendMail($subjet, $mails, $donnees, $pageUrl, $urlFiles);

            $this->changeEtat($chantier->getId(), 'Planifiee');
            $this->addFlash('message', 'Votre chantier a été créé avec succés !');
            return $this->redirectToRoute('app_affaire_me_nok');

        }
        if ($request->attributes->get('_route') == 'app_affaire_me_ok') {
            $affaires = $this->repoAffaires->findFinish();
        } elseif ($request->attributes->get('_route') == 'app_affaire_me_nok') {
            $affaires = $this->repoAffaires->findNotFinish();
        }
        $fichesManquantes = $this->affaireAdminController->recupFichesManquantes();
        $fichesNonVerrouillees = $this->repoFiche->findBy(['lockedAt' => null]);

        return $this->render('affaires/affaire.html.twig', [
            'affaires' => $affaires,
            'title' => 'Affaires',
            'data' => $data,
            'interventionsActuels' => $interventionsActuels,
            'form' => $form->createView(),
            'fichesManquantes' => $fichesManquantes,
            'fichesNonVerrouillees' => $fichesNonVerrouillees,
        ]);
    }

    #[Route("/Lhermitte/pieces/affaire/nok/{{affaire}}", name: "app_piece_affaire_nok")]
    #[Route("/Lhermitte/pieces/affaire/ok/{{affaire}}", name: "app_piece_affaire_ok")]

    public function pieceAffaire($affaire, Request $request): Response
    {
        if ($request->attributes->get('_route') == 'app_piece_affaire_ok') {
            $pieces = $this->repoAffairePiece->findBy(['etat' => 'Termine', 'affaire' => $affaire]);
        } elseif ($request->attributes->get('_route') == 'app_piece_affaire_nok') {
            $pieces = $this->repoAffairePiece->findBy(['affaire' => $affaire, 'closedAt' => null]);

        }
        $pieces = $this->mettreProduitsSurPieces($pieces);
        $form = $this->createForm(AffaireType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $affaire = $this->repoAffaires->findOneBy(['code' => $affaire]);
            $data = $form->getData();
            if ($data->getProgress()) {
                $affaire->setProgress($data->getProgress());
            }
            if ($data->getStart()) {
                $affaire->setStart(new DateTime($data->getStart()->format('Y-m-d H:i')));
            }
            if ($data->getEnd()) {
                $affaire->setEnd(new DateTime($data->getEnd()->format('Y-m-d H:i')));
            }
            if ($data->getDuration()) {
                $affaire->setDuration($data->getDuration());
            }
            if ($data->getbackgroundColor()) {
                $affaire->setbackgroundColor($data->getBackgroundColor());
            }
            if ($data->getTextColor()) {
                $affaire->setTextColor($data->getTextColor());
            }
            $em = $this->entityManager;
            $em->persist($affaire);
            $em->flush();
            $this->addFlash('message', 'Mise à jour effectuée avec succés');
            return $this->redirectToRoute('app_piece_affaire_nok', ['affaire' => $affaire->getCode()]);
        }
        $table = 'affaire';
        $id = $this->repoAffaires->findOneBy(['code' => $affaire])->getId();

        $affaire = $this->repoAffaires->findOneBy(['code' => $affaire]);

        $intervention = new InterventionMonteurs();
        $InterventionsMonteursForm = $this->createForm(InterventionsMonteursType::class, $intervention);
        $InterventionsMonteursForm->handleRequest($request);

        if ($InterventionsMonteursForm->isSubmitted() && $InterventionsMonteursForm->isValid()) {
            //dd($InterventionsMonteursForm->getData());
            $intervention->setCreatedAt(new DateTime())
                ->setUserCr($this->getUser())
                ->setCode($affaire);
            if ($InterventionsMonteursForm->getData()->getAdresse()) {
                $intervention->setAdresse($InterventionsMonteursForm->getData()->getAdresse());
            } else {
                if ($InterventionsMonteursForm->getData()->getPieces()) { // && $value->getPiece() != '99999999'
                    $adresses = [];
                    foreach ($InterventionsMonteursForm->getData()->getPieces() as $value) {
                        $adresses[] = $value->getAdresse();
                    }
                    $adresses = array_unique($adresses);
                    $adresse = '';
                    foreach ($adresses as $value) {
                        $adresse = $value . ' | ' . $adresse;
                    }
                }
                if (!$adresse) {
                    $adresse = 'Pas de donnée adresse';
                }
                $intervention->setAdresse($adresse);
            }
            $entityManager = $this->entityManager;
            $entityManager->persist($intervention);
            $entityManager->flush();

            // Ajouter les fichiers joints
            $files = $InterventionsMonteursForm->get('files')->getData();
            // On boucle sur les images
            if ($files) {
                foreach ($files as $file) {
                    // On génère un nouveau nom de fichier
                    $d = new DateTime();
                    $d = $d->format('Y-m-d H-i-s');
                    $filename = $file->getClientOriginalName();
                    $fichier = $filename . $d . '.' . $file->guessExtension();

                    // On copie le fichier dans le dossier uploads
                    $file->move(
                        $this->getParameter('doc_lhermitte_affaires'),
                        $fichier
                    );
                    // On crée l'image dans la base de données
                    $doc = new OthersDocuments();
                    $doc->setFile($fichier);
                    $doc->setTables('affaire');
                    $doc->setParametre($intervention->getId());
                    $doc->setCreatedAt(new DateTime);
                    $doc->setUser($this->getUser());
                    $doc->setIdentifiant($intervention->getCode()->getId());
                    $this->addFlash('message', 'Fichier ' . $filename . ' ajouté avec succés');
                    $entityManager = $this->entityManager;
                    $entityManager->persist($doc);
                    $entityManager->flush();
                }
            }

            if ($InterventionsMonteursForm->get('comment')->getData()) {
                $chat = new Chats;
                $chat->setCreatedAt(new \DateTime())
                    ->setUser($this->getUser())
                    ->setContent($InterventionsMonteursForm->get('comment')->getData())
                    ->setFonction('chatAffaire')
                    ->setIdentifiant($intervention->getCode()->getId())
                    ->setTables($intervention->getId());
                $entityManager = $this->entityManager;
                $entityManager->persist($chat);
                $entityManager->flush();

            }

            $this->changeEtat($affaire->getId(), 'Planifiee');

        }
        $Interventions = $this->repoIntervertionsMonteurs->findBy(['code' => $affaire]);
        $chats = $this->repoChats->findBy(['fonction' => 'chatAffaire', 'identifiant' => $affaire->getId()], ['createdAt' => 'DESC']);
        $docs = $this->repoDocs->findBy(['tables' => 'affaire', 'identifiant' => $id]);
        $retraits = $this->repoRetrait->findBy(['chantier' => $affaire->getCode()]);
        foreach ($retraits as $retrait) {
            $r = $this->repoArt->getEanStock(1, $retrait->getEan());

            // Définir les données récupérées comme propriétés supplémentaires sur l'objet $retrait
            // Ce code fontionne, c'est VisualStudio qui ne comprends pas.
            $retrait->ref = $r['ref'];
            $retrait->sref1 = $r['sref1'];
            $retrait->sref2 = $r['sref2'];
            $retrait->designation = $r['designation'];
            $retrait->uv = $r['uv'];
            $retrait->stock = $r['stock'];
        }

        return $this->render('affaires/pieceAffaire.html.twig', [
            // todo à voir pour filtrer l'état en fonction de la page affichée
            'piecesAffaires' => $pieces,
            'title' => 'piecesAffaires',
            'affaire' => $affaire,
            'form' => $form->createView(),
            'docs' => $docs,
            'chats' => $chats,
            'retraits' => $retraits,
            'InterventionsMonteursForm' => $InterventionsMonteursForm->createView(),
            'Interventions' => $Interventions,
        ]);
    }

    #[Route("/Lhermitte/update/affaire", name: "app_update_affaires")]

    public function update(): Response
    {
        $affaires = $this->repoMouv->getAffaires();
        foreach ($affaires as $value) {
            $affaire = $this->repoAffaires->findOneBy(['code' => $value['affaire']]);
            if (!$affaire) {
                $affaire = new Affaires;
                $affaire->setCode($value['affaire'])
                    ->setLibelle($value['libelle'])
                    ->setTiers($value['tiers'])
                    ->setNom($value['nom'])
                    ->setStart(new Datetime($value['dateCreation']))
                    ->setEtat('Nouvelle');
                $entityManager = $this->entityManager;
                $entityManager->persist($affaire);
                $entityManager->flush();

                $subjet = "Une nouvelle affaire a été créé => " . $affaire->getLibelle();
                $donnees = $affaire;
                $pageUrl = "affaires/mails/mailNewAffaire.html.twig";
                $mails = $this->emailTreatementService->treatementMails('app_affaires_admin', null, null);
                $urlFiles = [];
                $this->emailTreatementService->sendMail($subjet, $mails, $donnees, $pageUrl, $urlFiles);
            }
        }

        return $this->redirectToRoute('app_update_piece_affaires');

    }

    #[Route("/Lhermitte/update/pieces/affaire", name: "app_update_piece_affaires")]

    public function updatePieces(): Response
    {
        $affaires = $this->repoAffaires->findAll();
        foreach ($affaires as $affaire) {
            $pieces = $this->repoMouv->getPiecesAffaires($affaire->getCode());
            foreach ($pieces as $p) {
                $piece = $this->repoAffairePiece->findOneBy(['entId' => $p['id']]);

                if (!$piece) {
                    $piece = new AffairePiece;
                    $piece->setAffaire($p['affaire'])
                        ->setEntId($p['id'])
                        ->setAdresse($p['adresse'])
                        ->setTypePiece($p['typeP'])
                        ->setPiece($p['piece'])
                        ->setOp($p['op'])
                        ->setTransport($p['transport'])
                        ->setEtat('Nouvelle');
                    $entityManager = $this->entityManager;
                    $entityManager->persist($piece);
                    $entityManager->flush();

                    if ($affaire->getEnd()) {
                        $affaire->setEnd(null)
                            ->setEtat('A finir');
                        $entityManager = $this->entityManager;
                        $entityManager->persist($affaire);
                        $entityManager->flush();
                    }

                } else {
                    $piece->setAffaire($p['affaire'])
                        ->setEntId($p['id'])
                        ->setAdresse($p['adresse'])
                        ->setTypePiece($p['typeP'])
                        ->setPiece($p['piece'])
                        ->setOp($p['op'])
                        ->setTransport($p['transport']);
                    $entityManager = $this->entityManager;
                    $entityManager->persist($piece);
                    $entityManager->flush();
                }
            }
        }
        $this->addFlash('message', 'Mise à jour effectuée avec succés');
        return $this->redirectToRoute('app_affaire_me_nok');
    }

    #[Route("/Lhermitte/affaire/change/etat/{id}/{etat}", name: "app_affaire_change_etat")]

    public function changeEtat($id, $etat): Response
    {

        $affaire = $this->repoAffaires->findOneBy(['id' => $id]);
        $affaire->setEtat($etat);
        if ($etat == 'Termine') {
            $affaire->setEnd(new DateTime());
            $pieces = $this->repoAffairePiece->findBy(['affaire' => $affaire->getCode()]);
            foreach ($pieces as $value) {
                $this->changeEtatPiece($value->getId(), $etat);
            }
        } else {
            $affaire->setEnd(null);
        }
        $entityManager = $this->entityManager;
        $entityManager->persist($affaire);
        $entityManager->flush();

        $this->addFlash('message', 'Mise à jour effectuée avec succés');
        return $this->redirectToRoute('app_affaire_me_nok');

    }

    #[Route("/Lhermitte/affaire/piece/change/etat/{id}/{etat}", name: "app_affaire_piece_change_etat")]

    public function changeEtatPiece($id, $etat): Response
    {
        $piece = $this->repoAffairePiece->findOneBy(['id' => $id]);
        $piece->setEtat($etat);
        if ($etat == 'Termine') {
            $piece->setClosedAt(new DateTime());
        } else {
            $piece->setClosedAt(null);
        }
        $entityManager = $this->entityManager;
        $entityManager->persist($piece);
        $entityManager->flush();

        $this->addFlash('message', 'Mise à jour effectuée avec succés');
        return $this->redirectToRoute('app_piece_affaire_nok', ['affaire' => $piece->getAffaire()]);

    }

    #[Route("/Lhermitte/affaire/remove/intervention/{id}", name: "app_affaire_remove_intervention")]

    public function removeIntervention($id): Response
    {
        $intervention = $this->repoIntervertionsMonteurs->findOneBy(['id' => $id]);
        // suppression des fichiers de cette intervention
        $fichiers = $this->repoDocs->findBy(['tables' => 'affaire', 'Parametre' => $id, 'identifiant' => $intervention->getCode()->getId()]);
        if ($fichiers) {
            foreach ($fichiers as $fichier) {
                $chemin = $this->getParameter('doc_lhermitte_affaires') . '/' . $fichier->getFile();
                $entityManager = $this->entityManager;
                $entityManager->remove($fichier);
                $entityManager->flush();
                unset($chemin);
            }
        }

        // suppression des commentaires de cette intervention
        $commentaires = $this->repoChats->findBy(['fonction' => 'chatAffaire', 'identifiant' => $intervention->getCode()->getId(), 'tables' => $id]);
        if ($commentaires) {
            foreach ($commentaires as $commentaire) {
                $entityManager = $this->entityManager;
                $entityManager->remove($commentaire);
                $entityManager->flush();
            }
        }
        $affaire = $intervention->getCode()->getCode();
        $entityManager = $this->entityManager;
        $entityManager->remove($intervention);
        $entityManager->flush();

        $this->addFlash('message', 'Intervention supprimée avec succés');
        return $this->redirectToRoute('app_piece_affaire_nok', ['affaire' => $affaire]);

    }

    #[Route("/Lhermitte/affaire/edit/intervention/{id}/{affaire}", name: "app_affaire_edit_intervention")]

    public function editIntervention($id, $affaire, Request $request): Response
    {

        $intervention = $this->repoIntervertionsMonteurs->findOneBy(['id' => $id]);
        $InterventionsMonteursForm = $this->createForm(InterventionsMonteursType::class, $intervention);
        $InterventionsMonteursForm->handleRequest($request);

        if ($InterventionsMonteursForm->isSubmitted() && $InterventionsMonteursForm->isValid()) {
            $intervention->setCreatedAt(new DateTime())
                ->setUserCr($this->getUser());
            if ($InterventionsMonteursForm->getData()->getAdresse()) {
                $intervention->setAdresse($InterventionsMonteursForm->getData()->getAdresse());
            } else {
                if ($InterventionsMonteursForm->getData()->getPieces()) { // && $value->getPiece() != '99999999'
                    $adresses = [];
                    foreach ($InterventionsMonteursForm->getData()->getPieces() as $value) {
                        $adresses[] = $value->getAdresse();
                    }
                    $adresses = array_unique($adresses);
                    $adresse = '';
                    foreach ($adresses as $value) {
                        $adresse = $value . ' | ' . $adresse;
                    }
                }
                if (!$adresse) {
                    $adresse = 'Pas de donnée adresse';
                }
                $intervention->setAdresse($adresse);
            }
            $entityManager = $this->entityManager;
            $entityManager->persist($intervention);
            $entityManager->flush();
            $this->addFlash('message', 'Intervention modifiée avec succés');
        }

        return $this->render('affaires/editIntervention.html.twig', [
            'InterventionsMonteursForm' => $InterventionsMonteursForm->createView(),
            'title' => "Modification d'intervention",
            'intervention' => $intervention,
        ]);

    }

    #[Route("/Lhermitte/affaire/show/intervention/{id}", name: "app_affaire_show_intervention")]

    public function showIntervention($id, Request $request): Response
    {

        $produits = '';
        $intervention = $this->repoIntervertionsMonteurs->findOneBy(['id' => $id]);
        $ChatsForm = $this->createForm(ChatsType::class);
        $ChatsForm->handleRequest($request);

        if ($ChatsForm->isSubmitted() && $ChatsForm->isValid()) {
            $chat = $ChatsForm->getData();
            $chat->setCreatedAt(new \DateTime())
                ->setUser($this->getUser())
                ->setContent($chat->getContent())
                ->setFonction('chatAffaire')
                ->setIdentifiant($intervention->getCode()->getId())
                ->setTables($intervention->getId());
            $entityManager = $this->entityManager;
            $entityManager->persist($chat);
            $entityManager->flush();

            $subjet = "Un commentaire à été déposé => " . $intervention->getCode()->getLibelle();
            $donnees = $this->repoChats->findBy(['fonction' => 'chatAffaire', 'identifiant' => $intervention->getCode()->getId(), 'tables' => $intervention->getId()], ['createdAt' => 'DESC']);
            $emails = null;
            $equipes = $intervention->getEquipes();
            if ($equipes) {
                $equipesArray = $equipes->toArray(); // Convertir la collection en un tableau
                $emails = array_map(function ($equipe) {
                    return $equipe->getEmail();
                }, $equipesArray);
            }
            $pageUrl = "affaires/mails/mailNewCommentaire.html.twig";
            $mails = $this->emailTreatementService->treatementMails('app_affaires_admin', null, $emails);
            $urlFiles = [];
            // envoie du mail
            $this->emailTreatementService->sendMail($subjet, $mails, $donnees, $pageUrl, $urlFiles);

            $this->addFlash('message', 'Commentaire ajouté avec succès');
            return $this->redirectToRoute('app_affaire_show_intervention', ['id' => $id]);

        }
        $formFiles = $this->createForm(OthersDocumentsMultipleType::class);
        $formFiles->handleRequest($request);
        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            $files = $formFiles->get('file')->getData();
            // On boucle sur les images
            foreach ($files as $file) {
                // On génère un nouveau nom de fichier
                $d = new DateTime();
                $d = $d->format('Y-m-d H-i-s');
                $filename = $file->getClientOriginalName();
                $fichier = $filename . $d . '.' . $file->guessExtension();
                $search = $this->repoDocs->findOneBy(['identifiant' => $id, 'file' => $fichier]);
                if ($search == null) {
                    // On copie le fichier dans le dossier uploads
                    $file->move(
                        $this->getParameter('doc_lhermitte_affaires'),
                        $fichier
                    );
                    // On crée l'image dans la base de données
                    $doc = new OthersDocuments();
                    $doc->setFile($fichier);
                    $doc->setTables('affaire');
                    $doc->setParametre($intervention->getId());
                    $doc->setCreatedAt(new DateTime);
                    $doc->setUser($this->getUser());
                    $doc->setIdentifiant($intervention->getCode()->getId());
                    $this->addFlash('message', 'Fichier ' . $filename . ' ajouté avec succés');
                    $entityManager = $this->entityManager;
                    $entityManager->persist($doc);
                    $entityManager->flush();
                } else {
                    $this->addFlash('danger', 'Fichier ' . $filename . ' est déjà présent ! ce fichier n\'est pas sauvegardé !');
                }
            }
            $this->addFlash('message', 'Fichier(s) ajouté(s) avec succès');
            return $this->redirectToRoute('app_affaire_show_intervention', ['id' => $id]);
        }

        $docs = $this->repoDocs->findBy(['tables' => 'affaire', 'identifiant' => $intervention->getCode()->getId()]);
        $chats = $this->repoChats->findBy(['fonction' => 'chatAffaire', 'identifiant' => $intervention->getCode()->getId()], ['createdAt' => 'DESC']);
        $usersMes = $this->repoUsers->findby(['service' => 9]);
        $pieces = $intervention->getPieces();
        $ids = '';
        $i = 0;
        foreach ($pieces as $piece) {
            if ($i == 0) {
                $ids = $piece->getEntId();
            } else {
                $ids = $ids . ',' . $piece->getEntId();
            }
            $i++;
        }
        if ($i > 0) {
            $produits = $this->repoMouv->getDetailPiecesAffaires($ids);
        }
        $retraits = $this->repoRetrait->findBy(['chantier' => $intervention->getCode()->getCode()]);
        foreach ($retraits as $retrait) {
            $r = $this->repoArt->getEanStock(1, $retrait->getEan());

            // Définir les données récupérées comme propriétés supplémentaires sur l'objet $retrait
            // Ce code fontionne, c'est VisualStudio qui ne comprends pas.
            $retrait->ref = $r['ref'];
            $retrait->sref1 = $r['sref1'];
            $retrait->sref2 = $r['sref2'];
            $retrait->designation = $r['designation'];
            $retrait->uv = $r['uv'];
            $retrait->stock = $r['stock'];
        }

        return $this->render('affaires/showIntervention.html.twig', [
            'title' => "Voir intervention",
            'intervention' => $intervention,
            'produits' => $produits,
            'usersMes' => $usersMes,
            'docs' => $docs,
            'formFiles' => $formFiles->createView(),
            'chats' => $chats,
            'retraits' => $retraits,
            'ChatsForm' => $ChatsForm->createView(),
        ]);
    }

    #[Route("/Lhermitte/affaire/creer/fiche/intervention/{id}/{intervenant}/{createdAt}", name: "app_affaire_creer_fiche_intervention")]

    public function creerFicheIntervention($id, $intervenant, $createdAt, Request $request)
    {
        $fiche = new InterventionFicheMonteur;
        $fiche->setCreatedAt(new DateTime($createdAt))
            ->setCreatedBy($this->getUser())
            ->setHere(true)
            ->setIntervenant($this->repoUsers->findOneBy(['id' => $intervenant]))
            ->setIntervention($this->repoIntervertionsMonteurs->findOneBy(['id' => $id]));
        $entityManager = $this->entityManager;
        $entityManager->persist($fiche);
        $entityManager->flush();

        $this->addFlash('message', 'Fiche créé avec succès');
        return $this->redirectToRoute('app_affaire_edit_fiche_intervention', ['id' => $id, 'ficheId' => $fiche->getId()]);
    }

    #[Route("/Lhermitte/affaire/saisie/fiche/intervention/{id}", name: "app_affaire_saisie_fiche_intervention")]
    #[Route("/Lhermitte/affaire/edit/fiche/intervention/{id}/{ficheId}", name: "app_affaire_edit_fiche_intervention")]

    public function saisieFicheIntervention($id, Request $request, $ficheId = null): Response
    {

        // Si la fiche existe déjà, on la modifie et on la charge
        if ($request->attributes->get('_route') == 'app_affaire_saisie_fiche_intervention') {
            $searchFiche = $this->repoInterventionFicheMonteur->findOneBy([
                'createdAt' => new DateTime($request->request->get('intervention_fiche')['createdAt']),
                'intervenant' => $request->request->get('intervention_fiche')['intervenant'],
                'intervention' => $id,
            ]);
            if ($searchFiche) {
                $this->addFlash('warning', 'Une fiche existe déjà pour cette intervention, cet intervenant et cette date, elle a été chargée ...');
                return $this->redirectToRoute('app_affaire_edit_fiche_intervention', ['id' => $id, 'ficheId' => $searchFiche->getId()]);
            } else {
                $fiche = new InterventionFicheMonteur;
            }
        } elseif ($request->attributes->get('_route') == 'app_affaire_edit_fiche_intervention') {
            $fiche = $this->repoInterventionFicheMonteur->findOneBy(['id' => $ficheId]);
        }
        $formFicheIntervention = $this->createForm(InterventionFicheType::class, $fiche);
        $formFicheIntervention->handleRequest($request);
        if ($formFicheIntervention->isSubmitted() && $formFicheIntervention->isValid()) {
            if ($request->attributes->get('_route') == 'app_affaire_saisie_fiche_intervention') {
                $fiche->setIntervention($this->repoIntervertionsMonteurs->findOneBy(['id' => $id]));
                $fiche->setCreatedBy($this->getUser());
            } elseif ($request->attributes->get('_route') == 'app_affaire_edit_fiche_intervention') {
                //$fiche->setCreatedAt(new DateTime($fiche['createdAt']));
                $fiche->setCreatedBy($this->getUser());
            }
            $entityManager = $this->entityManager;
            $entityManager->persist($fiche);
            $entityManager->flush();
            if ($request->attributes->get('_route') == 'app_affaire_saisie_fiche_intervention') {
                $this->addFlash('message', 'Fiche créé avec succès');
            } elseif ($request->attributes->get('_route') == 'app_affaire_edit_fiche_intervention') {
                $this->addFlash('message', 'Fiche modifiée avec succès');
            }
            return $this->redirectToRoute('app_affaire_edit_fiche_intervention', ['id' => $id, 'ficheId' => $fiche->getId()]);
        }
        $heure = new InterventionFichesMonteursHeures;
        $formHeureIntervention = $this->createForm(InterventionFichesMonteursHeuresType::class, $heure);
        $formHeureIntervention->handleRequest($request);
        if ($formHeureIntervention->isSubmitted() && $formHeureIntervention->isValid()) {
            $heure->setCreatedAt(new DateTime())
                ->setCreatedBy($this->getUser())
                ->setInterventionFicheMonteur($fiche);
            $entityManager = $this->entityManager;
            $entityManager->persist($heure);
            $entityManager->flush();
            $this->addFlash('message', 'Heures déposée avec succès');
            return $this->redirectToRoute('app_affaire_edit_fiche_intervention', ['id' => $id, 'ficheId' => $fiche->getId()]);
        }

        $formCommentaire = $this->createForm(InterventionFicheCommentType::class, $fiche);
        $formCommentaire->handleRequest($request);
        if ($formCommentaire->isSubmitted() && $formCommentaire->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($fiche);
            $entityManager->flush();

            $this->addFlash('message', 'Commentaire déposé avec succès');
            return $this->redirectToRoute('app_affaire_edit_fiche_intervention', ['id' => $id, 'ficheId' => $fiche->getId()]);
        }
        //$fiches = $this->repoInterventionFicheMonteur->findAll();
        $fiche = $this->repoInterventionFicheMonteur->findOneBy(['id' => $ficheId]);
        $intervention = $this->repoIntervertionsMonteurs->findOneBy(['id' => $id]);
        $heures = $this->repoInterventionFichesMonteursHeures->findBy(['interventionFicheMonteur' => $ficheId]);
        return $this->render('affaires/saisieFicheIntervention.html.twig', [
            'title' => "Fiche d'intervention",
            'formFicheIntervention' => $formFicheIntervention->createView(),
            //'fiches' => $fiches,
            'formHeureIntervention' => $formHeureIntervention->createView(),
            'formCommentaire' => $formCommentaire->createView(),
            'heures' => $heures,
            'fiche' => $fiche,
            'intervention' => $intervention,
        ]);
    }

    #[Route("/Lhermitte/affaire/remove/fiche/intervention/{id}/{fiche}", name: "app_affaire_remove_fiche_intervention")]

    public function removeFicheIntervention($id, $fiche, Request $request): Response
    {
        // suppression des heures en rapport avec la fiche monteur
        $heures = $this->repoInterventionFichesMonteursHeures->findBy(['interventionFicheMonteur' => $fiche]);
        foreach ($heures as $heure) {
            $entityManager = $this->entityManager;
            $entityManager->remove($heure);
            $entityManager->flush();
        }
        // suppression de la fiche monteur
        $fiche = $this->repoInterventionFicheMonteur->findOneBy(['id' => $fiche]);
        $entityManager = $this->entityManager;
        $entityManager->remove($fiche);
        $entityManager->flush();

        $this->addFlash('message', 'Fiche supprimée avec succès');

        return $this->redirectToRoute('app_affaire_saisie_fiche_intervention', ['id' => $id]);
    }

    #[Route("/Lhermitte/affaire/remove/heure/intervention/{id}/{ficheId}/{heureId}", name: "app_affaire_remove_heure_intervention")]

    public function removeHeureIntervention($id, $ficheId, $heureId, Request $request): Response
    {
        $heure = $this->repoInterventionFichesMonteursHeures->findOneBy(['id' => $heureId]);
        $entityManager = $this->entityManager;
        $entityManager->remove($heure);
        $entityManager->flush();

        $this->addFlash('message', 'Horaire supprimé avec succès');
        return $this->redirectToRoute('app_affaire_edit_fiche_intervention', ['id' => $id, 'ficheId' => $ficheId]);
    }

    #[Route("/Lhermitte/affaire/verrouiller/fiche/intervention/{id}/{ficheId}", name: "app_affaire_verrouiller_fiche_intervention")]

    public function verrouillerFicheIntervention($id, $ficheId): Response
    {
        $fiche = $this->repoInterventionFicheMonteur->findOneBy(['id' => $ficheId]);
        $heures = $this->repoInterventionFichesMonteursHeures->findBy(['interventionFicheMonteur' => $ficheId]);
        if ($heures || $fiche->getHere() == false) {
            $fiche->setLockedAt(new DateTime())
                ->setLockedBy($this->getUser());
            $entityManager = $this->entityManager;
            $entityManager->persist($fiche);
            $entityManager->flush();

            $this->addFlash('message', 'Fiche verrouillée avec succès');
        } else {
            $this->addFlash('danger', "Il n'y a pas d'heures de déclarées sur cette fiche, merci de les renseigner ! Pour cela utilisez le bouton Déclarer Heures Déplac. Travaux");
        }
        return $this->redirectToRoute('app_affaire_me_nok');
    }

    // alimenter les piéces avec les produits qu'elle contient
    public function mettreProduitsSurPieces($pieces)
    {
        $lesPieces = [];
        foreach ($pieces as $piece) {
            $produits = $this->repoMouv->getDetailPiecesAffaires($piece->getEntId());
            $lesPieces[] = $piece = [
                'id' => $piece->getId(),
                'entId' => $piece->getEntId(),
                'adresse' => $piece->getAdresse(),
                'typePiece' => $piece->getTypePiece(),
                'piece' => $piece->getPiece(),
                'op' => $piece->getOp(),
                'transport' => $piece->getTransport(),
                'etat' => $piece->getEtat(),
                'affaire' => $piece->getAffaire(),
                'produits' => $produits,
                'interventions' => $piece->getInterventionMonteurs(),
            ];
        }
        return $lesPieces;
    }
}
