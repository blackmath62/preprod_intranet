<?php

namespace App\Controller;

use App\Entity\Main\Societe;
use App\Form\EditSocieteType;
use App\Repository\Main\SocieteRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class AdminSocieteController extends AbstractController
{
    /**
     * @Route("/admin/societe", name="app_admin_societe")
     */
    public function index(Societe $societe = null, Request $request, SocieteRepository $repo, EntityManagerInterface $manager)
    {
        if (!$societe) {
            $societe = new Societe();
        }
        $form = $this->createFormBuilder($societe)
            ->add("nom")
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $societe->setCreatedAt(new DateTime());
            $manager->persist($societe);
            $manager->flush();
        }
        $societes = $repo->findAll();

        // tracking user page for stats
        //$tracking = $request->attributes->get('_route');
        //$this->setTracking($tracking);

        return $this->render('admin_societe/index.html.twig', [
            'controller_name' => 'AdminSocieteController',
            'societes' => $societes,
            'formSociete' => $form->createView(),
            'title' => "Administration des Sociétés",
        ]);
    }

    /**
     * @Route("/admin/societe/delete/{id}",name="app_delete_societe")
     */
    public function deleteSociete($id, Request $request)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(Societe::class);
        $societeId = $repository->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($societeId);
        $em->flush();

        // tracking user page for stats
        //$tracking = $request->attributes->get('_route');
        //$this->setTracking($tracking);

        return $this->redirect($this->generateUrl('app_admin_societe'));
    }
    /**
     * @Route("/admin/societe/edit/{id}",name="app_edit_societe")
     */
    public function editSociete(Societe $societe, Request $request)
    {
        $form = $this->createForm(EditSocieteType::class, $societe);
        $form->handleRequest($request);

        // tracking user page for stats
        //$tracking = $request->attributes->get('_route');
        //$this->setTracking($tracking);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($societe);
            $em->flush();

            $this->addFlash('message', 'Société modifié avec succès');
            return $this->redirectToRoute('app_admin_societe');

        }
        return $this->render('admin_societe/edit_societe.html.twig', [
            'societeEditForm' => $form->createView(),
            'societe' => $societe,
            'title' => 'Edition des Sociétés',
        ]);
    }
}
