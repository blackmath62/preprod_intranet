<?php

namespace App\Controller;

use App\Form\StatesDateFilterType;
use App\Repository\Divalto\MouvRepository;
use App\Repository\Main\fscListMovementRepository;
use App\Repository\Main\MovBillFscRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FscExtractPeriodeController extends AbstractController
{
    #[Route("/fsc/extract/periode", name: "app_fsc_extract_periode")]

    public function index(Request $request, fscListMovementRepository $repoAchat, MovBillFscRepository $repoVente, MouvRepository $repoMouv): Response
    {
        $achats = '';
        $ventes = '';
        $detailAchats = '';
        $detailVentes = '';
        $form = $this->createForm(StatesDateFilterType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $start = $form->getData()['startDate']->format('Y-m-d');
            $end = $form->getData()['endDate']->format('Y-m-d');

            $achats = $repoMouv->getExtractPeriodByTypeFsc($start, $end, 'F', 'resume');
            $detailAchats = $repoMouv->getExtractPeriodByTypeFsc($start, $end, 'F', 'detail');

            $ventes = $repoMouv->getExtractPeriodByTypeFsc($start, $end, 'C', 'resume');
            $detailVentes = $repoMouv->getExtractPeriodByTypeFsc($start, $end, 'C', 'detail');
        }

        return $this->render('fsc_extract_periode/index.html.twig', [
            'form' => $form->createView(),
            'title' => 'Extraction Période',
            'achats' => $achats,
            'ventes' => $ventes,
            'detailAchats' => $detailAchats,
            'detailVentes' => $detailVentes,
        ]);
    }
}
