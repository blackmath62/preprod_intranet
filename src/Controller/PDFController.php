<?php

namespace App\Controller;

use Knp\Snappy\Pdf;
use Twig\Environment;
use App\Repository\Main\AnnuaireRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PDFController extends Command
{
    
    private $twig;
    private $pdf;

    public function __construct(AnnuaireRepository $repo, Environment $twig, Pdf $pdf)
    {
        $this->twig = $twig;
        $this->pdf = $pdf;
    }
    
    /**
     * @Route("/pdf", name="app_pdf")
     */
    public function index(AnnuaireRepository $repo)
    {
        
        $annuaires = $repo->findAll();
        $html = $this->twig->render('annuaire/index.html.twig', [
            'annuaires' => $annuaires,
            'title' => 'PDF'            
            ]);
            $pdf = $this->pdf->getOutputFromHtml($html);
            //return $this->render('pdf/index.html.twig', [
            //    'controller_name' => 'PDFController',
            //    ]);
            }
            
            protected function execute(InputInterface $input, OutputInterface $output)
            {
        $annuaires = $this->repo->findAll();
        $html = $this->twig->render('annuaire/index.html.twig', [
            'annuaires' => $annuaires,
            'title' => 'PDF']);            

    $pdf = $this->pdf->getOutputFromHtml($html);
    }
    
}
