<?php
namespace Status\V1\Rpc\Ping;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\FactoryInterface;
use OAuth2\Server as OAuth2Server;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Provider\UserId;

class PingControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authController = new PingController(
            $this->getOAuth2ServerFactory($container),
            $container->get(UserId\AuthenticationService::class)
        );

        $authController->setApiProblemErrorResponse(
            $this->marshalApiProblemErrorResponse($container)
        );

        return $authController;

    }

    /**
     * @param ServiceLocatorInterface $controllers
     * @param null|string $name
     * @param null|string $requestedName
     * @return PingController
     */
    public function createService(ServiceLocatorInterface $controllers, $name = null, $requestedName = null)
    {
        if ($controllers instanceof AbstractPluginManager) {
            $container = $controllers->getServiceLocator() ?: $controllers;
        } else {
            $container = $controllers;
        }

        $requestedName = $requestedName ?: PingController::class;

        return $this($container, $requestedName);
    }

    /**
     * Retrieve the OAuth2\Server factory.
     *
     * For BC purposes, if the OAuth2Server service returns an actual
     * instance, this will wrap it in a closure before returning it.
     *
     * @param ContainerInterface $container
     * @return callable
     */
    private function getOAuth2ServerFactory(ContainerInterface $container)
    {
        $oauth2ServerFactory = $container->get('ZF\OAuth2\Service\OAuth2Server');
        if (! $oauth2ServerFactory instanceof OAuth2Server) {
            return $oauth2ServerFactory;
        }

        return function () use ($oauth2ServerFactory) {
            return $oauth2ServerFactory;
        };
    }

    /**
     * Determine whether or not to render API Problem error responses.
     *
     * @param ContainerInterface $container
     * @return bool
     */
    private function marshalApiProblemErrorResponse(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            return false;
        }

        $config = $container->get('config');

        return (isset($config['zf-oauth2']['api_problem_error_response'])
            && $config['zf-oauth2']['api_problem_error_response'] === true);
    }
}
