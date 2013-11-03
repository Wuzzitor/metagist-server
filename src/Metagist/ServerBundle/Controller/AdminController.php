<?php

namespace Metagist\ServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Metagist\ServerBundle\Entity\Branding;
use Metagist\ServerBundle\Form\BrandingType;
use Metagist\WorkerBundle\Command\ScanCommand;
use JMS\JobQueueBundle\Entity\Job;

/**
 * Branding controller.
 *
 * @Route("/admin", service="metagist.admin.controller")
 */
class AdminController extends BaseController
{
    /**
     * Admin index view.
     * 
     * @Template()
     * @Route("/", name="admin")
     * @return array
     */
    public function indexAction()
    {
        return array(
            'uncategorized' => $this->serviceProvider->packages()->uncategorized()
        );
    }
    
    /**
     * Updates package info by invoking the worker.
     * 
     * @param string $author
     * @param string $name
     * @return string
     * @Route("/update/{author}/{name}", name="admin_update")
     */
    public function update($author, $name)
    {
        $package = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
        $entityManager = $this->getDoctrine()->getEntityManager();
        try {
            $job = new Job(ScanCommand::COMMAND, array($package->getIdentifier()));
            $entityManager->persist($job);
            $entityManager->flush($job);
        } catch (\Exception $exception) {
            $this->notifyUser(
                'error', 'Error while updating the package: ' . $exception->getMessage()
            );
            $this->serviceProvider->logger()->error('Exception: ' . $exception->getMessage());
            return $this->serviceProvider->redirect('/');
        }

        $this->notifyUser(
            'success', 'The package ' . $package->getIdentifier() . ' will be updated. Thanks.'
        );
        return $this->redirectToPackageView($package);
    }
}
