<?php

namespace Status\V1\Rpc\Ping;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\ContentNegotiation\ViewModel;
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use RuntimeException;
use InvalidArgumentException;
use Zend\Http\PhpEnvironment\Request as PhpEnvironmentRequest;
use ZF\OAuth2\Provider\UserId\UserIdProviderInterface;

class PingController extends AbstractActionController
{
    /**
     * @var OAuth2Server
     */
    protected $server;
    protected $serverFactory;

    /**
     * Constructor
     *
     * @param callable $serverFactory
     * @param UserIdProviderInterface $userIdProvider
     */
    public function __construct($serverFactory, UserIdProviderInterface $userIdProvider)
    {
        if (! is_callable($serverFactory)) {
            throw new InvalidArgumentException(sprintf(
                'OAuth2 Server factory must be a PHP callable; received %s',
                (is_object($serverFactory) ? get_class($serverFactory) : gettype($serverFactory))
            ));
        }
        $this->serverFactory  = $serverFactory;
        $this->userIdProvider = $userIdProvider;
    }

    public function pingAction()
    {
        //@todo pra que esse parametro?!
        $server = $this->getOAuth2Server('/oauth');
        // Handle a request for an OAuth2.0 Access Token and send the response to the client
        if (!$server->verifyResourceRequest($this->getOAuth2Request())) {
            $response = $server->getResponse();
            return $this->getApiProblemResponse($response);
        }

        return new ViewModel([
            'ack' => time()
        ]);
    }

    /**
     * Indicate whether ApiProblemResponse or oauth2 errors should be returned.
     *
     * Boolean true indicates ApiProblemResponse should be returned (the
     * default), while false indicates oauth2 errors (per the oauth2 spec)
     * should be returned.
     *
     * @param bool $apiProblemErrorResponse
     */
    public function setApiProblemErrorResponse($apiProblemErrorResponse)
    {
        $this->apiProblemErrorResponse = (bool) $apiProblemErrorResponse;
    }

    /**
     * Map OAuth2Response to ApiProblemResponse
     *
     * @param OAuth2Response $response
     * @return ApiProblemResponse
     */
    protected function getApiProblemResponse(OAuth2Response $response)
    {
        $parameters       = $response->getParameters();
        $errorUri         = isset($parameters['error_uri'])         ? $parameters['error_uri']         : null;
        $error            = isset($parameters['error'])             ? $parameters['error']             : null;
        $errorDescription = isset($parameters['error_description']) ? $parameters['error_description'] : null;

        return new ApiProblemResponse(
            new ApiProblem(
                $response->getStatusCode(),
                $errorDescription,
                $errorUri,
                $error
            )
        );
    }

    /**
     * Create an OAuth2 request based on the ZF2 request object
     *
     * Marshals:
     *
     * - query string
     * - body parameters, via content negotiation
     * - "server", specifically the request method and content type
     * - raw content
     * - headers
     *
     * This ensures that JSON requests providing credentials for OAuth2
     * verification/validation can be processed.
     *
     * @return OAuth2Request
     */
    protected function getOAuth2Request()
    {
        $zf2Request = $this->getRequest();
        $headers = $zf2Request->getHeaders();

        // Marshal content type, so we can seed it into the $_SERVER array
        $contentType = '';
        if ($headers->has('Content-Type')) {
            $contentType = $headers->get('Content-Type')->getFieldValue();
        }

        // Get $_SERVER superglobal
        $server = [];
        if ($zf2Request instanceof PhpEnvironmentRequest) {
            $server = $zf2Request->getServer()->toArray();
        } elseif (!empty($_SERVER)) {
            $server = $_SERVER;
        }
        $server['REQUEST_METHOD'] = $zf2Request->getMethod();

        // Seed headers with HTTP auth information
        $headers = $headers->toArray();
        if (isset($server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $server['PHP_AUTH_USER'];
        }
        if (isset($server['PHP_AUTH_PW'])) {
            $headers['PHP_AUTH_PW'] = $server['PHP_AUTH_PW'];
        }

        // Ensure the bodyParams are passed as an array
        $bodyParams = $this->bodyParams() ?: [];

        return new OAuth2Request(
            $zf2Request->getQuery()->toArray(),
            $bodyParams,
            [], // attributes
            [], // cookies
            [], // files
            $server,
            $zf2Request->getContent(),
            $headers
        );
    }

    /**
     * Retrieve the OAuth2\Server instance.
     *
     * If not already created by the composed $serverFactory, that callable
     * is invoked with the provided $type as an argument, and the value
     * returned.
     *
     * @param string $type
     * @return OAuth2Server
     * @throws RuntimeException if the factory does not return an OAuth2Server instance.
     */
    private function getOAuth2Server($type)
    {
        if ($this->server instanceof OAuth2Server) {
            return $this->server;
        }

        $server = call_user_func($this->serverFactory, $type);
        if (!$server instanceof OAuth2Server) {
            throw new RuntimeException(sprintf(
                'OAuth2\Server factory did not return a valid instance; received %s',
                (is_object($server) ? get_class($server) : gettype($server))
            ));
        }
        $this->server = $server;
        return $server;
    }
}
