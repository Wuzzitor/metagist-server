<?php

namespace Metagist\ServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Metagist\ServerBundle\Entity\Branding;
use Metagist\ServerBundle\Form\BrandingType;

/**
 * Branding controller.
 *
 * @Route("/admin/brandings")
 */
class BrandingController extends Controller
{

    /**
     * Lists all Branding entities.
     *
     * @Route("/", name="admin_branding")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MetagistServerBundle:Branding')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Branding entity.
     *
     * @Route("/create", name="admin_branding_create")
     * @Method("POST")
     * @Template("MetagistServerBundle:Branding:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Branding();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->compileBrandings();

            return $this->redirect($this->generateUrl('admin_branding_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Branding entity.
     *
     * @param Branding $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Branding $entity)
    {
        $form = $this->createForm(new BrandingType(), $entity, array(
            'action' => $this->generateUrl('admin_branding_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Branding entity.
     *
     * @Route("/new", name="admin_branding_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Branding();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Branding entity.
     *
     * @Route("/{id}", name="admin_branding_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetagistServerBundle:Branding')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Branding entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Branding entity.
     *
     * @Route("/{id}/edit", name="admin_branding_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MetagistServerBundle:Branding')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Branding entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a Branding entity.
     *
     * @param Branding $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Branding $entity)
    {
        $form = $this->createForm(new BrandingType(), $entity, array(
            'action' => $this->generateUrl('admin_branding_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Branding entity.
     *
     * @Route("/update/{id}", name="admin_branding_update")
     * @Template("MetagistServerBundle:Branding:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getRepo()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Branding entity.');
        }

        $flashBag = $this->get('session')->getFlashBag();
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $entity->upload();
            $flashBag->add('success', 'Branding updated.');
            
            $webDir = $this->get('kernel')->getRootDir() . '/../web';
            $file = $webDir . '/media/cache/my_thumb/images/'.basename($entity->getWebPath());
            @unlink($file);
        
            $this->compileBrandings();
            return $this->redirect($this->generateUrl('admin_branding_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Branding entity.
     *
     * @Route("/{id}", name="admin_branding_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MetagistServerBundle:Branding')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Branding entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_branding'));
    }

    /**
     * Creates a form to delete a Branding entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('admin_branding_delete', array('id' => $id)))
                ->setMethod('DELETE')
                ->add('submit', 'submit', array('label' => 'Delete'))
                ->getForm()
        ;
    }

    /**
     * Returns the branding repo.
     * 
     * @return \Metagist\ServerBundle\Entity\BrandingRepository
     */
    private function getRepo()
    {
        return $this->get('doctrine')->getEntityManager()->getRepository('MetagistServerBundle:Branding');
    }

    /**
     * Compiles less to css.
     * 
     * @throws \Exception
     */
    private function compileBrandings()
    {
        $sourcePath = $this->get('kernel')->getCacheDir();
        $lessFile   = $this->getRepo()->compileAllToLess($sourcePath);
        $targetPath = $this->get('kernel')->getRootDir() . '/../web/css/brandings.css';
        $lessComp   = new \lessc();
        file_put_contents($targetPath, $lessComp->compileFile($lessFile));
    }

}
