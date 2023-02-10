<?php

namespace App\Controller;

use App\Form\OthersDocumentsType;
use App\Repository\Main\OthersDocumentsRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AuditAfnorController extends AbstractController
{
    /**
     * @Route("/audit/afnor", name="app_audit_afnor")
     */
    public function index(Request $request, OthersDocumentsRepository $repo, SluggerInterface $slugger): Response
    {
        // tracking user page for stats
        $tracking = $request->attributes->get('_route');
        //$this->setTracking($tracking);

        $form = $this->createForm(OthersDocumentsType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $table = $tracking;
            if ($file) {
                $d = new DateTime();

                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . $d->format('Y-m-d H-i-s') . '.' . $file->guessExtension();
                $path = 'doc/Lhermitte_freres/Afnor/';
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                // Move the file to the directory where brochures are stored
                try {
                    $file->move($path, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                // updates the 'Filename' property to store the PDF file name
                // instead of its contents
                $doc = $form->getData();
                $doc->setCreatedAt(new \DateTime())
                    ->setTables($table)
                    ->setFile($newFilename)
                    ->setIdentifiant(1)
                    ->setUser($this->getUser());
                $em = $this->getDoctrine()->getManager();
                $em->persist($doc);
                $em->flush();
            }

            $this->addFlash('message', 'Document ajouté avec succés');
            return $this->redirectToRoute('app_audit_afnor');
        }

        $docs = $repo->findBy(['tables' => $tracking]);

        return $this->render('audit_afnor/index.html.twig', [
            'form' => $form->createView(),
            'docs' => $docs,
            'title' => 'Documents Audit Afnor',
        ]);
    }

    /**
     * @Route("/audit/afnor/view/{id}", name="app_audit_afnor_show")
     */

    public function DocsReferentielAfnorShow(int $id, OthersDocumentsRepository $repo, Request $request)
    {
        $doc = $repo->findOneBy(['id' => $id]);

        // tracking user page for stats
        //$tracking = $request->attributes->get('_route');
        //$this->setTracking($tracking);

        return $this->render('faq/faq_show.html.twig', [
            'doc' => $doc,
            'title' => 'Document',
        ]);
    }

    /**
     * @Route("/audit/afnor/delete/{id}", name="app_audit_afnor_delete")
     */

    public function DocsReferentielAfnorDelete(int $id, OthersDocumentsRepository $repo, Request $request)
    {
        $doc = $repo->findOneBy(['id' => $id]);

        // tracking user page for stats
        //$tracking = $request->attributes->get('_route');
        //$this->setTracking($tracking);

        $em = $this->getDoctrine()->getManager();
        $em->remove($doc);
        $em->flush();

        $this->addFlash('message', 'Document supprimé avec succés');
        return $this->redirectToRoute('app_audit_afnor');
    }
}
