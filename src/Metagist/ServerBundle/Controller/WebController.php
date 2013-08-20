<?php
namespace Metagist\ServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Web controller
 * 
 * @Route("/", service="metagist.web.controller")
 */
class WebController extends Controller
{
    /**
     * service provider
     * 
     * @var \Metagist\ServerBundle\Controller\ServiceProvider
     */
    private $serviceProvider;
    
    /**
     * Constructor
     * 
     * @param \Metagist\ServerBundle\Controller\ServiceProvider $serviceProvider
     */
    public function __construct(ServiceProvider $serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
    }
    
   /**
     * Routing setup.
     * 
     * 
     */
    protected function initRoutes()
    {
        $routes = array(
            'errors'        => array('match' => '/errors', 'method' => 'errors'),
            'loginNotice'   => array('match' => '/login', 'method' => 'loginNotice'),
            'login'         => array('match' => '/auth/login', 'method' => 'login'),
            'logout'        => array('match' => '/auth/logout', 'method' => 'logout'),
            'ratings'       => array('match' => '/ratings/{author}/{name}', 'method' => 'ratings'),
            'ratings-pp'    => array('match' => '/ratings/{author}/{name}/{page}', 'method' => 'ratings'),
            'rate'          => array('match' => '/rate/{author}/{name}', 'method' => 'rate'),
            'contribute-list' => array('match' => '/contribute/list/{author}/{name}', 'method' => 'contributeList'),
            'contribute'    => array('match' => '/contribute/{author}/{name}/{group}', 'method' => 'contribute'),
            'package'       => array('match' => '/package/{author}/{name}', 'method' => 'package'),
            'search'        => array('match' => '/search', 'method' => 'search'),
            'search-page'   => array('match' => '/search/{query}/{page}', 'method' => 'search'),
            'update'        => array('match' => '/update/{author}/{name}', 'method' => 'update'),
            'latest'        => array('match' => '/latest', 'method' => 'latest'),
            'about'         => array('match' => '/about', 'method' => 'about'),
        );

        foreach ($routes as $name => $data) {
            $this->serviceProvider
                ->match($data['match'], array($this, $data['method']))
                ->bind($name);
        }

        $this->registerErrorFunction();
    }

    /**
     * Default.
     * 
     * @return string
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        $repo    = $this->serviceProvider->metainfo();
        $ratings = $this->serviceProvider->ratings();
        return array(
            'featured' => $repo->byGroup('featured'),
            'best' => array(),
            'latestRatings' => $ratings->latest(1),
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
        $repo    = $this->serviceProvider->metainfo();
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
            'user' => $this->getUser()
        );
    }

    /**
     * Github oAuth, redirect to use the github strategy.
     */
    public function login()
    {
        return $this->redirect('/auth/login/github');
    }

    /**
     * Logout clears the session.
     * 
     * @return RedirectResponse
     */
    public function logout()
    {
        $this->serviceProvider->session()->invalidate();
        $this->serviceProvider->session()->clear();
        
        return $this->serviceProvider->redirect(
                $this->serviceProvider['url_generator']->generate('homepage')
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
            'ratings' => $this->serviceProvider->ratings()->byPackage($package, 0, 5)
        );
    }
    
    /**
     * Updates package info by invoking the worker.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function update($author, $name)
    {
        $flashBag = $this->serviceProvider->session()->getFlashBag();
        try {
            $package = $this->getPackage($author, $name);
            $this->serviceProvider->getApi()->worker()->scan($author, $name);
        } catch (\Exception $exception) {
            $flashBag->add(
                'error',
                'Error while updating the package: ' . $exception->getMessage()
            );
            $this->serviceProvider->logger()->error('Exception: ' . $exception->getMessage());
            return $this->serviceProvider->redirect('/');
        }
        
        $flashBag->add(
            'success',
            'The package ' . $package->getIdentifier() . ' will be updated. Thanks.'
        );
        return $this->serviceProvider->redirect('/package/' . $package->getIdentifier());
    }

    /**
     * Shows the package ratings.
     * 
     * @param sting  $author
     * @param string $name
     * @return string
     */
    public function ratings($author, $name, $page = 1)
    {
        $package  = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
        $ratings  = $this->serviceProvider->ratings()->byPackage($package);
        $routeGen = function($page) { return '/ratings/'.$page;};
        $pager    = $this->getPaginationFor($ratings);
        $pager->setCurrentPage($page);
        $view     = new TwitterBootstrapView();
        return $this->serviceProvider->render(
            'ratings.html.twig', array(
                'package' => $package,
                'ratings' => $pager,
                'pagination' => $view->render($pager, $routeGen)
            )
        );
    }
    
    /**
     * Rate a package.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function rate($author, $name, Request $request)
    {
        $package  = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
        $flashBag = $this->serviceProvider->session()->getFlashBag();
        $user     = $this->serviceProvider->security()->getToken()->getUser();
        $rating   = $this->serviceProvider->ratings()->byPackageAndUser($package, $user);
        $form     = $this->getFormFactory()->getRateForm($package->getVersions(), $rating);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data     = $form->getData();
                $data['package'] = $package;
                $data['user_id'] = $user->getId();
                $rating = Rating::fromArray($data);
                $this->serviceProvider->ratings()->save($rating);
                $flashBag->add('success', 'Thanks.');
                return $this->serviceProvider->redirect('/package/' . $package->getIdentifier());
            } else {
                $form->addError(new FormError('Please check the entered value.'));
            }
        }
        
        return $this->serviceProvider->render(
            'rate.html.twig', array(
                'package' => $package,
                'form'    => $form->createView()
            )
        );
    }

    /**
     * Lists the categories and groups to contribute to.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function contributeList($author, $name)
    {
        $package = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
        //retrieve the related infos.
        $metaInfos = $this->serviceProvider->metainfo()->byPackage($package);
        $package->setMetaInfos($metaInfos);
        
        return $this->serviceProvider->render(
            'contribute-list.html.twig', 
            array(
                'package' => $package,
                'categories' => $this->serviceProvider->categories()
            )
        );
    }
    
    /**
     * Contribute to the package (provide information).
     * 
     * @param string  $author
     * @param string  $name
     * @param string  $group
     * @param Request $request
     * @return string
     */
    public function contribute($author, $name, $group, Request $request)
    {
        $package     = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
        $flashBag    = $this->serviceProvider->session()->getFlashBag();
        $category    = $this->serviceProvider->categories()->getCategoryForGroup($group);
        $groups      = $this->serviceProvider->categories()->getGroups($category);
        $groupData   = $groups[$group];
        $form        = $this->getFormFactory()->getContributeForm(
                            $package->getVersions(), $groupData->type
                       );
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data     = $form->getData();
                $metaInfo = MetaInfo::fromValue($group, $data['value'], $data['version']);
                $metaInfo->setPackage($package);
                
                try {
                    $this->serviceProvider->metainfo()->save($metaInfo);
                    $flashBag->add('success', 'Info saved. Thank you.');
                } catch (Symfony\Component\Security\Core\Exception\AccessDeniedException $exception) {
                    $this->serviceProvider->logger()->warn($exception->getMessage());
                    $flashBag->add('error', 'Access denied to ' . $group);
                }
                
                return $this->serviceProvider->redirect('/package/' . $package->getIdentifier());
            } else {
                $form->addError(new FormError('Please check the entered value.'));
            }
        }


        return $this->serviceProvider->render(
            'contribute.html.twig', 
            array(
                'package' => $package,
                'form' => $form->createView(),
                'category' => $category,
                'group' => $group,
                'type'  => $groupData->type,
                'description' => $groupData->description,
            )
        );
    }
    
    /**
     * Search for a package.
     * 
     * @param Request $request
     * @return string
     * @Route("/search", name="search")
     * @Template()
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        if ($query == '*') {
            $query = '';
        }
        $page  = $request->get('page');
        if (intval($page) == 0) {
            $page = 1;
        }
        @list ($author, $name) = explode('/', $query);
        $package = null;
        try {
            $package = $this->serviceProvider->packages()->byAuthorAndName($author, $name);
            if ($package !== null) {
                $url = '/' . $package->getIdentifier();
                return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
            } else {
                /*
                 * Creating a dummy package, triggers the creation process if
                 * user follows the link.
                 */
                $dummy = new Package($author . '/' . $name);
            }
        } catch (\Exception $exception) {
            $this->serviceProvider->logger()->info('Search failed: ' . $exception->getMessage());
        }
        
        $packages = $this->serviceProvider->packages()->byIdentifierPart($author);
        
        $routeGenerator = function($page) use ($query) {
            if ($query == '') {
                $query = '*';
            }
            return '/search/' . urlencode($query) . '/'.$page;
        };
        $pagerfanta = $this->getPaginationFor($packages);
        $pagerfanta->setCurrentPage($page);
        $view       = new TwitterBootstrapView();
        
        return $this->serviceProvider->render(
            'search.html.twig', 
            array(
                'query' => $query,
                'dummy' => isset($dummy) ? $dummy : null,
                'packages' => $pagerfanta,
                'pagination' => $view->render($pagerfanta, $routeGenerator)
            )
        );
    }
    
    /**
     * Just displays the notice that the user has to be logged in.
     * 
     * @return string
     */
    public function loginNotice()
    {
        return $this->serviceProvider->render('login.html.twig');
    }

    /**
     * 
     * @return void
     */
    protected function registerErrorFunction()
    {
        $app = $this->serviceProvider;
        $this->serviceProvider->error(function (\Exception $exception, $code) use ($app) {
                if ($app['debug']) {
                    return;
                }

                switch ($code) {
                    case 404:
                        $message = 'The requested page could not be found.';
                        break;
                    default:
                        $message = 'We are sorry, but something went terribly wrong.';
                }

                return new Response($message, $code);
            });
    }

    /**
     * Returns the form factory.
     * 
     * @return \Metagist\FormFactory
     */
    protected function getFormFactory()
    {
        return new FormFactory(
            $this->serviceProvider['form.factory'],
            $this->serviceProvider[ServiceProvider::CATEGORY_SCHEMA]
        );
    }
    
    /**
     * Creates a pagination for the given collection.
     * 
     * @param \Doctrine\Common\Collections\Collection $collection
     * @return Pagerfanta
     */
    protected function getPaginationFor(Collection $collection, $maxPerPage = 25)
    {
        $adapter = new DoctrineCollectionAdapter($collection);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        return $pagerfanta;
    }
}
