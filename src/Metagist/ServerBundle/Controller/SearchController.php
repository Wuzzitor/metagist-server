<?php
namespace Metagist\ServerBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Metagist\ServerBundle\TWBS\TwitterBootstrapView;
use Metagist\ServerBundle\Entity\Metainfo;
use Metagist\ServerBundle\Entity\Package;

/**
 * Web controller
 * 
 * @Route("/", service="metagist.search.controller")
 */
class SearchController extends BaseController
{
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
     * Lists packages of a category.
     * 
     * @param Request $request
     * @return string
     * @Route(
     *     "/category/{name}/{page}",
     *     defaults={"page" = 1},
     *     requirements={"page" = "\d+", "name" = "\w+"},
     *     name="category"
     * )
     * @Template()
     */
    public function categoryAction($name, $page)
    {
        $that = $this;
        $routeGenerator = function($page) use ($that, $name) {
            return $that->generateUrl('category', array('name' => $name, 'page' => $page));
        };
        
        $catRepo = $this->getDoctrine()->getRepository('MetagistServerBundle:Category');
        $category = $catRepo->findOneBy(array('name' => $name));
        if (!$category) {
            $this->notifyUser('error', 'Unknown category');
            return $this->redirect($this->generateUrl('homepage'));
        }
        $packages = $category->getPackages();
        $pagerfanta = $this->getPaginationFor($packages);
        $pagerfanta->setCurrentPage($page);
        $view = new TwitterBootstrapView();
        
        return array(
            'categories' => $this->getCategories(),
            'category' => $category,
            'packages' => $pagerfanta,
            'pagination' => $view->render($pagerfanta, $routeGenerator)
        );
    }
}