<?php
/**
 * ExceptionListener.php
 */
namespace Metagist\ServerBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Psr\Log\LoggerInterface;

/**
 * Exception listener
 * 
 * @link https://symfonybricks.com/en/brick/custom-exception-page-404-not-found-and-other-exceptions
 */
class ExceptionListener
{
    /**
     * twig templating
     * 
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface 
     */
    private $templateEngine;

    /**
     * the logger
     * 
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     * 
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(EngineInterface $templating, LoggerInterface $logger)
    {
        $this->templateEngine = $templating;
        $this->logger         = $logger;
    }

    /**
     * Change the view based on the exception type.
     * 
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $template  = 'MetagistServerBundle:Exception:exception.html.twig';
        $exception = $event->getException();
        $response  = new Response();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
            $template = 'MetagistServerBundle:Exception:error404.html.twig';
            $this->logger->error($exception->getMessage());
        } else {
            $response->setStatusCode(500);
            $this->logger->critical($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        // set response content
        $response->setContent(
            $this->templateEngine->render(
                $template, array('exception' => $exception)
            )
        );

        // set the new $response object to the $event
        $event->setResponse($response);
    }
}
