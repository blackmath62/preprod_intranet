<?php

namespace App\Controller;

use DateTime;
use App\Entity\Main\statusHoliday;
use App\Form\EditHolidayStatusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\Main\statusHolidayRepository;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_ADMIN")
 */

class AdminStatusHolidayController extends AbstractController
{
    /**
     * @Route("/admin/status/holiday", name="app_admin_status_holiday")
     */
    public function index(Request $request, statusHolidayRepository $repo, EntityManagerInterface $manager): Response
    {     
                $holidayStatus = new statusHoliday();
         
            $form = $this->createFormBuilder($holidayStatus)
                ->add("name", TextType::class,[
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Merci de saisir un nom de status de congés'
                        ])
                        ],
                        'required' => true,
                        'label' => 'Nom du status de congés'
                        ])
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $holidayStatus->setCreatedAt(new DateTime());
                $manager->persist($holidayStatus);
                $manager->flush();
            }
            $holidayStatus = $repo->findAll();
            
            // tracking user page for stats
            $tracking = $request->attributes->get('_route');
            $this->setTracking($tracking);

            return $this->render('admin_status_holiday/index.html.twig', [
                'holidayStatus' => $holidayStatus,
                'formholidayStatus' => $form->createView(),
                'title' => "Administration des Status de Congés"
            ]);
    }
    /**
     * @Route("/admin/status/holiday/delete/{id}",name="app_delete_status_holiday")
     */
    public function deleteHolidayStatus($id, Request $request)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(statusHoliday::class);
        $holidayId = $repository->find($id);
         
        $em = $this->getDoctrine()->getManager();
        $em->remove($holidayId);
        $em->flush();        

        // tracking user page for stats
            $tracking = $request->attributes->get('_route');
            $this->setTracking($tracking);

        return $this->redirect($this->generateUrl('app_admin_status_holiday'));
    }
    /**
     * @Route("/admin/status/holiday/edit/{id}",name="app_edit_status_holiday")
     */
    public function editSociete(statusHoliday $holidayStatus, Request $request)
    {
        $form = $this->createForm(EditHolidayStatusType::class, $holidayStatus);
            $form->handleRequest($request);

            // tracking user page for stats
            $tracking = $request->attributes->get('_route');
            $this->setTracking($tracking);
            
            if($form->isSubmitted() && $form->isValid()){
                $em = $this->getDoctrine()->getManager();
                $em->persist($holidayStatus);
                $em->flush();

                $this->addFlash('message', 'Status de congés modifié avec succès');
                return $this->redirectToRoute('app_admin_status_holiday');

            }
            return $this->render('admin_status_holiday/edit_status_holiday.html.twig',[
                'holidayStatusEditForm' => $form->createView(),
                'holidayStatus' => $holidayStatus,
                'title' => 'Edition des Types de congés'
            ]);
    }
}