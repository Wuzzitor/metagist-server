<?php
namespace Metagist\ServerBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;

//use Metagist\ServerBundle\Security\Authentication\Token\ApiUserToken;
//use Metagist\Api\OAuthValidator;
use Metagist\Api\Factory;
use Psr\Log\LoggerInterface;

/**
 * Api security listener
 * 
 * 
 */
class ApiListener implements ListenerInterface
{
    /**
     * context
     * @var \Symfony\Component\Security\Core\SecurityContext 
     */
    private $securityContext;
    
    /**
     * auth manager
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager
     */
    private $authenticationManager;
    
    /**
     * api service factory
     * 
     * @var \Metagist\Api\Factory
     */
    private $apiFactory;
    
    /**
     * logger
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    
    /**
     * Constructor
     * 
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface $authenticationManager
     * @param \Metagist\Api\Factory $apiFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        SecurityContextInterface $securityContext, 
        AuthenticationManagerInterface $authenticationManager,
        Factory $apiFactory,
        LoggerInterface $logger
    ) {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->apiFactory = $apiFactory;
        $this->logger = $logger;
    }

    public function handle(GetResponseEvent $event)
    {
        $validator = $this->apiFactory->getOauthValidator();
        try {
            $validator->validateRequest($this->apiFactory->getIncomingRequest());
        } catch (\Metagist\Api\Exception $exception) {
            $this->logger->error("Request validation failed: " . $exception->getMessage());
            return;
        }
        
        $user  = new \Metagist\User($validator->getConsumerKey(), \Metagist\User::ROLE_SYSTEM);
        $token = new PreAuthenticatedToken($user, '', 'api');
        
        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

            $this->logger->info("User authenticated for consumer " . $user->getUsername());
            return;
        } catch (AuthenticationException $failed) {
            $this->logger->error("Authentication failed: " . $failed->getMessage());
        }

        // By default deny authorization
        $response = new Response();
        $response->setStatusCode(403);
        $event->setResponse($response);
    }
}