<?php

namespace Metagist\ServerBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Metagist\ServerBundle\TWBS\TwitterBootstrapView;
use Metagist\ServerBundle\Entity\Rating;
use Metagist\ServerBundle\Entity\Metainfo;
use Metagist\ServerBundle\Entity\Package;

/**
 * Web controller
 * 
 * @Route("/", service="metagist.web.controller")
 */
class WebController extends BaseController
{
    /**
     * Default.
     * 
     * @return string
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return array(
            'packages' => $this->serviceProvider->packages()->random(20),
            'categories' => $this->getCategories()
        );
    }

    /**
     * Features packages.
     * 
     * @return string
     * @Route("/featured", name="featured")
     * @Template()
     */
    public function featuredAction()
    {
        $repo = $this->serviceProvider->metainfo();
        return array(
            'featured' => $repo->byGroup('featured'),
            'categories' => $this->getCategories()
        );
    }

    /**
     * Show the latest updates and ratings.
     * 
     * @return string
     * @Route("/latest", name="latest")
     * @Template()
     */
    public function latestAction()
    {
        $repo = $this->serviceProvider->metainfo();
        $ratings = $this->serviceProvider->ratings();
        return array(
            'latestUpdates' => $repo->latest(),
            'latestRatings' => $ratings->latest(5),
        );
    }

    /**
     * Show the about info
     * 
     * @return string
     * @Route("/about", name="about")
     * @Template()
     */
    public function aboutAction()
    {
        return array();
    }

    /**
     * Show the user profile
     * 
     * @return string
     * @Route("/me", name="profile")
     * @Template()
     */
    public function profileAction()
    {
        return array(
            'user' => $this->getUser(),
            'ratings' => $this->serviceProvider->ratings()->byUser($this->getUser())
        );
    }

    /**
     * Shows package info.
     * 
     * @param string $author
     * @param string $name
     * @return string
     * @Route("/package/{author}/{name}", name="package")
     * @Template()
     */
    public function packageAction($author, $name)
    {
        $package = $this->serviceProvider->getPackage($author, $name);

        return array(
            'package' => $package,
            'categories' => $this->serviceProvider->categories(),
            'ratings' => $this->serviceProvider->ratings()->byPackage($package, 0, 3),
            'consumers' => $this->serviceProvider->dependencies()->getConsumersOf($package)
        );
    }

    /**
     * Shows a users profile
     * 
     * @param string $name
     * @return string
     * @Route("/user/{name}", name="user")
     * @Template()
     */
    public function userAction($name)
    {
        $repo = $this->getDoctrine()->getEntityManager()->getRepository('MetagistServerBundle:User');
        $user = $repo->findOneBy(array('username' => $name));

        if (!$user) {
            return $this->redirect('/');
        }

        return array(
            'user' => $user,
            'ratings' => $this->serviceProvider->ratings()->byUser($user)
        );
    }

    /**
     * Search for a package.
     * 
     * @param Request $request
     * @return string
     * @Route(
     *     "/search/{page}/{query}",
     *     defaults={"page" = 1, "query" = ""},
     *     requirements={"page" = "\d+"},
     *     name="search"
     * )
     * @Route("/search?query={query}")
     * @Template()
     * @link http://www.terrymatula.com/development/2013/some-packagist-api-hacks/
     */
    public function searchAction($page, $query, Request $request)
    {

        if (empty($query)) {
            $query = $request->query->get('query');
            if (empty($query)) {
                $this->notifyUser('error', 'Please enter a search query.');
                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        @list ($author, $name) = explode('/', $query);
        $package = null;
        try {
            $package = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
            if ($package !== null) {
                return $this->redirectToPackageView($package);
            }
        } catch (\Exception $exception) {
            $this->serviceProvider->logger()->info('Search failed: ' . $exception->getMessage());
        }

        $api = $this->serviceProvider->getPackagistApiClient();
        $response = $api->search($query, array('page' => $page));


        $packages = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($response as $result) {
            /* @var $result \Packagist\Api\Result\Result */
            $identifier = $result->getName();
            list ($author, $name) = Package::splitIdentifier($identifier);
            $package = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
            if (!$package) {
                $package = new Package($identifier);
                $package->setDescription($result->getDescription());
            }
            $packages->add($package);
        }

        $that = $this;
        $routeGenerator = function($page) use ($that, $query) {
            return $that->generateUrl('search', array('query' => urlencode($query), 'page' => $page));
        };
        $pagerfanta = $this->getPaginationFor($packages);
        $pagerfanta->setCurrentPage($page);
        $view = new TwitterBootstrapView();

        return array(
            'query' => $query,
            'packages' => $pagerfanta,
            'pagination' => $view->render($pagerfanta, $routeGenerator)
        );
    }

    /**
     * Just displays the notice that the user has to be logged in.
     * 
     * @return array
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        return array();
    }

    protected function getCategories()
    {
        return array(
            'featured' => $this->generateUrl('featured')
        );
    }

}
