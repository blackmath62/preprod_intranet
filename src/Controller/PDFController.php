<?php

namespace App\Controller;

use App\Entity\Main\Users;
use App\Repository\Main\FAQRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Snappy\Pdf;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class PdfController extends AbstractController
{
    /**
     * @Route("/pdf/faq/{id}", name="app_send_pdf_faq")
     */

    public function SendFaqPdf($id , MailerInterface $mailer, Pdf $pdf, FAQRepository $repo): Response
    {
        
            $faq = $repo->findOneBy(['id' => $id]);
            $user = $this->getUser()->getPseudo();
            $htmlPdf = $this->renderView('pdf/pdfFaq.html.twig', ['faq' => $faq]);
            $html = $this->renderView('mails/MailFaq.html.twig', ['faq' => $faq, 'user' => $user]);
            $pdf = $pdf->getOutputFromHtml($htmlPdf);
            $email = (new Email())
                ->from('intranet@groupe-axis.fr')
                ->to($this->getUser()->getEmail())
                ->subject('Tutoriel PDF : ' . $faq->getTitle())
                ->html($html)
                ->attach($pdf, 'Tutoriel'. $faq->getTitle() .'.pdf');
            $mailer->send($email);

            $this->addFlash('message', 'Le Tutoriel vous a été envoyé par mail en version PDF !');
            return $this->redirectToRoute('app_faq');

    }

}