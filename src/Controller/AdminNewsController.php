<?php

namespace App\Controller;

use App\Entity\Main\News;
use App\Form\AdminNewsType;
use App\Repository\Main\NewsRepository;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 */

class AdminNewsController extends AbstractController
{
    /**
     * @Route("/admin/news", name="app_admin_news")
     */
    public function index(Request $request, NewsRepository $repo): Response
    {
        // user tracking
        //$route = $request->attributes->get('_route');
        //$this->setTracking($route);

        $news = new News();
        $form = $this->createForm(AdminNewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // on assigne la date de création
            $news->setCreatedAt(new DateTime())
                ->setUser($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($news);
            $em->flush();
            $this->addFlash('message', 'Actualité ajoutée avec succès');
            unset($news);
        }

        return $this->render('admin_news/index.html.twig', [
            'title' => 'news',
            'form' => $form->createView(),
            'news' => $repo->findAll(),
        ]);
    }
    /**
     * @Route("/admin/news/edit/{id}", name="app_edit_news")
     */
    public function edit(Request $request, News $news)
    {

        $form = $this->createForm(AdminNewsType::class, $news);
        $form->handleRequest($request);

        // tracking user page for stats
        //$tracking = $request->attributes->get('_route');
        //$this->setTracking($tracking);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($news);
            $em->flush();

            $this->addFlash('message', 'Publication modifiée avec succès');
            return $this->redirectToRoute('app_admin_news');

        }
        return $this->render('admin_news/edit_news.html.twig', [
            'title' => "Modification de la publication",
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/admin/news/delete/{id}", name="app_delete_news")
     */
    public function delete(Request $request, News $news)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($news);
        $em->flush();

        //$route = $request->attributes->get('_route');
        //$this->setTracking($route);

        $this->addFlash('message', 'La publication a été Supprimée avec succès');
        return $this->redirectToRoute('app_admin_news');
    }
}
