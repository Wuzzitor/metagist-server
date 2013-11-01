<?php

namespace Metagist\ServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\Collection;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;
use Pagerfanta\Pagerfanta;
use Metagist\ServerBundle\TWBS\TwitterBootstrapView;
use Metagist\ServerBundle\Entity\Rating;
use Metagist\ServerBundle\Entity\Metainfo;
use Metagist\ServerBundle\Entity\Package;

/**
 * Web controller
 * 
 * @Route("/", service="metagist.contribute.controller")
 */
class ContributeController extends BaseController
{
    /**
     * Shows the package ratings.
     * 
     * @param sting  $author
     * @param string $name
     * @return string
     * @Route("/ratings/{author}/{name}", name="ratings")
     * @Template()
     */
    public function ratingsAction($author, $name, $page = 1)
    {
        $package = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
        $ratings = $this->serviceProvider->ratings()->byPackage($package);
        $routeGen = function($page) {
            return '/ratings/' . $page;
        };
        $pager = $this->getPaginationFor($ratings);
        $pager->setCurrentPage($page);
        $view = new TwitterBootstrapView();

        return array(
            'package' => $package,
            'ratings' => $pager,
            'pagination' => $view->render($pager, $routeGen)
        );
    }

    /**
     * Rate a package.
     * 
     * @param string $author
     * @param string $name
     * @return string
     * @Route("/contribute/rate/{author}/{name}", name="rate")
     * @Template()
     */
    public function rateAction($author, $name)
    {
        $request = $this->getRequest();
        $package = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
        $user = $this->getUser();
        $rating = $this->serviceProvider->ratings()->byPackageAndUser($package, $user);
        $form = $this->getFormFactory()->getRateForm($package->getVersions(), $rating);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                if ($rating === null) {
                    $data['package'] = $package;
                    $rating = Rating::fromArray($data);
                }
                $rating->setUser($user);
                $this->serviceProvider->ratings()->save($rating);
                $average = $this->serviceProvider->ratings()->getAverageForPackage($package);
                $package->setOverallRating($average);
                $this->serviceProvider->packages()->save($package);

                $this->notifyUser('success', 'Thank you for your feedback.');
                return $this->redirectToPackageView($package);
            } else {
                $form->addError(new FormError('Please check the entered value.'));
            }
        }

        return array(
            'package' => $package,
            'form' => $form->createView()
        );
    }

    /**
     * Lists the categories and groups to contribute to.
     * 
     * @param string $author
     * @param string $name
     * @return string
     * @Route("/contribute-list/{author}/{name}", name="contribute-list")
     * @Template()
     */
    public function contributeListAction($author, $name)
    {
        $package = $this->serviceProvider->packages()->byAuthorAndName($author, $name);

        return array(
            'package' => $package,
            'categories' => $this->serviceProvider->categories()
        );
    }

    /**
     * Contribute to the package (provide information).
     * 
     * @param string  $author
     * @param string  $name
     * @param string  $group
     * @return string
     * @Route("/contribute/{author}/{name}/{group}", name="contribute")
     * @Template()
     */
    public function contributeAction($author, $name, $group)
    {
        $request = $this->getRequest();
        $package = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
        $category = $this->serviceProvider->categories()->getCategoryForGroup($group);
        $groups = $this->serviceProvider->categories()->getGroups($category);
        $groupData = $groups[$group];
        $form = $this->getFormFactory()->getContributeForm(
            $package->getVersions(), $groupData->type
        );

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $metaInfo = Metainfo::fromValue($group, $data['value'], $data['version']);
                $metaInfo->setPackage($package);
                $metaInfo->setUser($this->getUser());

                try {
                    $this->serviceProvider->metainfo()->save($metaInfo);
                    $this->notifyUser('success', 'Info saved. Thank you.');
                } catch (Symfony\Component\Security\Core\Exception\AccessDeniedException $exception) {
                    $this->serviceProvider->logger()->warn($exception->getMessage());
                    $this->notifyUser('error', 'Access denied to ' . $group);
                }

                return $this->redirectToPackageView($package);
            } else {
                $form->addError(new FormError('Please check the entered value.'));
            }
        }

        return array(
            'package' => $package,
            'form' => $form->createView(),
            'category' => $category,
            'group' => $group,
            'type' => $groupData->type,
            'description' => $groupData->description,
        );
    }
    
        /**
     * Returns the form factory.
     * 
     * @return \Metagist\FormFactory
     */
    protected function getFormFactory()
    {
        return new \Metagist\ServerBundle\Form\FormFactory(
            $this->get('form.factory'), $this->serviceProvider->categories()
        );
    }
}
