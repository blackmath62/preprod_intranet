<?php

namespace App\Controller;

use App\Form\YearMonthType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Divalto\ArtRepository;
use App\Repository\Divalto\EntRepository;
use App\Repository\Divalto\MouvRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class DebRobyController extends AbstractController
{   
    
    

    public function test(EntityManagerInterface $entityManager, MouvRepository $repo): Response
    {        
        // DIVALTO cette requête fonctionne correctement

        $numeroFacture = new \DateTime('2021-01-01');
        $numFactMax = new \DateTime('2021-01-31');

        $entityManager = $this->getDoctrine()->getManager('divaltoreel');
        $Articles = $repo->test($entityManager, $numeroFacture, $numFactMax);

        //echo get_class($repo);

        return $this->render('deb_roby/index.html.twig', [
            'controller_name' => 'DebRobyController',
            'Articles' => $Articles,
            'title' => 'Deb Roby'
            ]);
        }
        
        public function test2(EntityManagerInterface $entityManager, MouvRepository $repo): Response
        {        
            // DIVALTO cette requête fonctionne correctement
            
            $numeroFacture = new \DateTime('2021-01-01');
            $numFactMax = new \DateTime('2021-01-31');
            
            //$entityManager = $this->getDoctrine()->getManager('divaltoreel');
            $Articles = $repo->test2();
            
            //echo get_class($repo);
            
            return $this->render('deb_roby/index.html.twig', [
                'controller_name' => 'DebRobyController',
                'Articles' => $Articles,
                'title' => 'Deb Roby'
                ]);
            }
            
            public function entityArticles(ArtRepository $repo){
                
                $em = $this->getDoctrine()->getManager('divaltoreel');
                $Articles = $repo->entityArticles($em);
                
                return $this->render('deb_roby/index.html.twig', [
                    'controller_name' => 'DebRobyController',
                    'Articles' => $Articles,
                    'title' => 'Test Entity Articles'
                    ]);
                }
                /**
                 * @Route("/roby/deb", name="app_deb_roby")
                 */
                public function getDebRobyByMonth(EntRepository $repo, Request $request){
                    
                    $form = $this->createForm(YearMonthType::class);
                    $form->handleRequest($request);
                    // initialisation de mes variables
                    $annee = date("Y");
                    $mois = date("m")-1;

                    if($form->isSubmitted() && $form->isValid()){
                        $annee = $form->getData()['year'];
                        $mois = $form->getData()['month'];
                        $debs = $repo->getDebRobyByMonth($annee,$mois);
                    }else{
                        $debs = $repo->getDebRobyByMonth($annee,$mois);
                    }
                
                    return $this->render('deb_roby/index.html.twig', [
                        'debs' => $debs,
                        'title' => 'DEB Roby par mois',
                        'monthYear' => $form->createView()
                        ]);

                }
            }
