<?php

namespace Metagist\ServerBundle\Controller;

use Metagist\ServerBundle\Entity\Package;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * base controller
 * 
 */
abstract class BaseController extends Controller
{

    /**
     * service provider
     * 
     * @var ServiceProvider
     */
    protected $serviceProvider;

    /**
     * Constructor
     * 
     * @param ServiceProvider $serviceProvider
     */
    public function __construct(ServiceProvider $serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
    }

    /**
     * Redirects to the package view.
     * 
     * @param Package $package
     * @return RedirectResponse
     */
    protected function redirectToPackageView(Package $package)
    {
        return $this->redirect(
            $this->generateUrl('package', array('author' => $package->getAuthor(), 'name' => $package->getName()))
        );
    }

    /**
     * Send a flash message to the user.
     * 
     * @param string $type error|success
     * @param string $message
     */
    protected function notifyUser($type, $message)
    {
        $this->serviceProvider->session()->getFlashBag()->add($type, $message);
    }
}
