<?php

namespace Ens\JobeetBundle\Controller;

use Ens\JobeetBundle\Entity\Job;
use Ens\JobeetBundle\Form\JobType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Job controller.
 *
 * @Route("job")
 */
class JobController extends Controller
{

    /**
     * Creates a new job entity.
     *
     * @Route("/create", name="job_create")
     * @Method({"POST"})
     */
    public function createAction(Request $request)
    {
        $entity = new Job();
        $form = $this->createForm(JobType::class, $entity);

        //hydrate l'objet form. 
        $form->handleRequest($request);
        
        if ($form->isValid())
        {
            return $this->redirect($this->generateUrl('job_preview', array(
                                'company' => $entity->getCompanySlug(),
                                'location' => $entity->getLocationSlug(),
                                'token' => $entity->getToken(),
                                'position' => $entity->getPositionSlug()
            )));
        }

        return $this->render('EnsJobeetBundle:Job:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Lists all job entities.
     *
     * @Route("/", name="job_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('EnsJobeetBundle:Category')->getWithJobs();

        foreach ($categories as $category)
        {
            $category->setActiveJobs($em->getRepository('EnsJobeetBundle:Job')->getActiveJobs($category->getId(), $this->container->getParameter('max_jobs_on_homepage')));
        }

        return $this->render('EnsJobeetBundle:Job:index.html.twig', array(
                    'categories' => $categories
        ));
    }

    /**
     * Creates a new job entity.
     *
     * @Route("/new", name="job_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {      
        $entity = new Job();
        $form = $this->createForm(JobType::class, $entity, array(
            'action' => $this->generateUrl('job_create')));
        
        return $this->render('EnsJobeetBundle:Job:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));


        //        en cas d'erreur
        //        $job = new Job();
        //        $form = $this->createForm('Ens\JobeetBundle\Form\JobType', $job);
        //        $form->handleRequest($request);
        //
        //        if ($form->isSubmitted() && $form->isValid())
        //        {
        //            $em = $this->getDoctrine()->getManager();
        //            $em->persist($job);
        //            $em->flush($job);
        //
        //            return $this->redirectToRoute('job_show', array('id' => $job->getId()));
        //        }
        //
        //        return $this->render('job/new.html.twig', array(
        //                    'job' => $job,
        //                    'form' => $form->createView(),
        //        ));
    }

    /**
     * Finds and displays a job entity.
     *
     * @Route("/{company}/{location}/{id}/{position}", name="job_show", requirements={"id": "\d+"})
     * @Method("GET")
     */
    public function showAction(Job $job)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('EnsJobeetBundle:Job')->getActiveJob($job->getId());

        $deleteForm = $this->createDeleteForm($job);

        return $this->render('job/show.html.twig', array(
                    'job' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Finds and displays a job entity.
     *
     * @Route("/{company}/{location}/{token}/{position}", name="job_preview", requirements={"token": "\w+"})
     * @Method("GET")
     */
    public function previewAction($token)
    {
        // ...

        $deleteForm = $this->createDeleteForm($entity->getId());
        $publishForm = $this->createPublishForm($entity->getToken());

        return $this->render('EnsJobeetBundle:Job:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
                    'publish_form' => $publishForm->createView(),
        ));
    }

    /**
     * Finds and displays a job entity.
     *
     * @Route("/{company}/{location}/{token}", name="job_publish", requirements={"token": "\w+"})
     * @Method("POST")
     */
    public function publishAction($token)
    {
        $form = $this->createPublishForm($token);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid())
        {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);

            if (!$entity)
            {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $entity->publish();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->setFlash('notice', 'Your job is now online for 30 days.');
        }

        return $this->redirect($this->generateUrl('ens_job_preview', array(
                            'company' => $entity->getCompanySlug(),
                            'location' => $entity->getLocationSlug(),
                            'token' => $entity->getToken(),
                            'position' => $entity->getPositionSlug()
        )));
    }

    private function createPublishForm($token)
    {
        return $this->createFormBuilder(array('token' => $token))
                        ->add('token', 'hidden')
                        ->getForm()
        ;
    }

    /**
     * Displays a form to edit an existing job entity.
     *
     * @Route("/{token}/edit", name="job_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Job $job)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);

        if (!$entity)
        {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $editForm = $this->createForm(new JobType(), $entity);
        $deleteForm = $this->createDeleteForm($token);

        return $this->render('EnsJobeetBundle:Job:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to update an existing job entity.
     *
     * @Route("/{token}/update", name="job_edit")
     * @Method({"GET", "POST"})
     */
    public function updateAction($token)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);

        if (!$entity)
        {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $editForm = $this->createForm(new JobType(), $entity);
        $deleteForm = $this->createDeleteForm($token);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid())
        {
            // ...

            return $this->redirect($this->generateUrl('ens_job_preview', array(
                                'company' => $entity->getCompanySlug(),
                                'location' => $entity->getLocationSlug(),
                                'token' => $entity->getToken(),
                                'position' => $entity->getPositionSlug()
            )));
        }

        return $this->render('EnsJobeetBundle:Job:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a job entity.
     *
     * @Route("/{token}/delete", name="job_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Job $job)
    {
        $form = $this->createDeleteForm($token);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid())
        {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('EnsJobeetBundle:Job')->findOneByToken($token);

            if (!$entity)
            {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ens_job'));
    }

    /**
     * Creates a form to delete a job entity.
     *
     * @param Job $job The job entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Job $job)
    {
        return $this->createFormBuilder(array('token' => $token))
                        ->add('token', 'hidden')
                        ->getForm();
    }

    public function setTokenValue()
    {
        if (!$this->getToken())
        {
            $this->token = sha1($this->getEmail() . rand(11111, 99999));
        }
    }

}
