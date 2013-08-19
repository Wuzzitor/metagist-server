<?php
namespace Metagist\ServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Metagist\Api\ServerInterface;
use Metagist\MetainfoInterface;
use Metagist\Api\RequestValidator;

/**
 * Api Controller.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @Route("/api", service="metagist.api.controller")
 */
class ApiController extends Controller implements ServerInterface
{
    /**
     * service provider
     * 
     * @var \Metagist\ServerBundle\Controller\ServiceProvider
     */
    private $serviceProvider;
    
    /**
     * request validator
     * 
     * @var \Metagist\Api\RequestValidator
     */
    private $validator;
    
    /**
     * Constructor
     * 
     * @param \Metagist\ServerBundle\Controller\ServiceProvider $serviceProvider
     */
    public function __construct(ServiceProvider $serviceProvider, RequestValidator $validator)
    {
        $this->serviceProvider = $serviceProvider;
        $this->validator = $validator;
    }
    
    /**
     * Index: returns the available routes
     * 
     * @Route("/", name="api-homepage")
     * @Method({"GET"})
     * @return string
     */
    public function indexAction()
    {
        $calls = array(
            'api-homepage'      => array('match' => '/api', 'method' => 'index'),
            'api-package'       => array('match' => '/api/package/{author}/{name}', 'method' => 'package'),
            'api-pushInfo'      => array('match' => '/api/pushInfo/{author}/{name}', 'method' => 'pushInfo'),
        );
        return new JsonResponse($calls);
    }
    
    /**
     * Returns the package content as json.
     * 
     * @param string $author
     * @param string $name
     * @return string
     * @Route("/package/{author}/{name}", name="api-package")
     */
    public function packageAction($author, $name)
    {
        return $this->package($author, $name);
    }
    
    /**
     * Returns the package content as json.
     * 
     * @param string $author
     * @param string $name
     * @return string
     */
    public function package($author, $name)
    {
        try {
            $package = $this->serviceProvider->getPackage($author, $name);
            $serializer = $this->serviceProvider->getApiFactory()->getSerializer();
            $body = $serializer->serialize($package, 'json');
            $code = 200;
        } catch (\Metagist\Api\Exception $exception) {
            $body = $exception->getMessage();
            $code = 404;
        }
        
        return $this->json($body, $code);
    }
    
    /**
     * Receive metainfo updates from a worker.
     * 
     * @param string $author
     * @param string $name
     * @param \Metagist\MetaInfo $info
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/pushInfo/{author}/{name}", name="api-pushInfo")
     * @Method({"POST"})
     */
    public function pushInfoAction($author, $name, MetainfoInterface $info = null)
    {
        return $this->pushInfo($author, $name, $info);
    }
    
    /**
     * Receive metainfo updates from a worker.
     * 
     * @param string $author
     * @param string $name
     * @param \Metagist\MetaInfo $info
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function pushInfo($author, $name, MetainfoInterface $info = null)
    {
        $factory = $this->serviceProvider->getApiFactory();
        $request = $factory->getIncomingRequest();
        /* @var $request \Guzzle\Http\Message\EntityEnclosingRequest */
        if (!$request instanceof \Guzzle\Http\Message\EntityEnclosingRequest) {
            return $this->json('POST required:', 300);
        }
        
        //validate oauth
        try {
            $this->validator->validateRequest($request);
        } catch (\Metagist\Api\Exception $exception) {
            $this->serviceProvider->logger()->warning('Error authorizing a pushInfo request: ' . $exception->getMessage());
            return $this->json('Authorization failed: ' . $exception->getMessage(), 403);
        }
        
        //validate json integrity
        try {
            $validator = $factory->getSchemaValidator();
            $validator->validateRequest($request, 'pushInfo');
        } catch (\Metagist\Api\Validation\Exception $exception) {
            $this->serviceProvider->logger()->warning('Error validating a pushInfo request: ' . $exception->getMessage());
            return $this->json('Invalid content: ' . $exception->getMessage(), 400);
        }
        
        $this->serviceProvider->logger()->info('Received signed and schema-valid pushInfo request.');
        
        //check package
        $package = $this->serviceProvider->getPackage($author, $name);
        if ($package == null) {
            $message = 'Unknown package ' . $author . '/' . $name;
            $this->serviceProvider->logger()->warning($message);
            return $this->json($message, 404);
        }
        
        $metaInfo = $this->extractMetaInfoFromRequest($request);
        if ($metaInfo === null) {
            return $this->serviceProvider->json('parsing error', 500);
        }
        $metaInfo->setPackage($package);
        
        try {
            $this->serviceProvider->metainfo()->save($metaInfo, 1);
        } catch (\Symfony\Component\Security\Core\Exception\AccessDeniedException $exception) {
            $this->serviceProvider->logger()->warning('PushInfo: ' . $exception->getMessage());
            return $this->json($exception->getMessage(), 403);
        }
        
        $this->serviceProvider->packages()->save($package);
        return $this->json(
            'Received info on ' . $metaInfo->getGroup() . ' for package ' . $package->getIdentifier()
        );
    }
    
    /**
     * Parses the body payload.
     * 
     * @param \Guzzle\Http\Message\EntityEnclosingRequestInterface $request
     * @return null
     */
    protected function extractMetaInfoFromRequest(EntityEnclosingRequestInterface $request)
    {
        $json = $request->getBody()->__toString();
        $data = json_decode($json, true);
        return \Metagist\Metainfo::fromArray($data['info']);
    }
    
    /**
     * Creates a json response.
     * 
     * @param string $body
     * @param int    $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function json($body, $code = 200) 
    {
        $response = \Symfony\Component\HttpFoundation\Response::create(
            $body, $code, array('application/json')
        );
        return $response;
    }
}