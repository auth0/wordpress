<?php

namespace Http\Mock;

use Http\Client\Common\HttpAsyncClientEmulator;
use Http\Client\Exception;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestMatcher;
use Http\Message\ResponseFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * An implementation of the HTTP client that is useful for automated tests.
 *
 * This mock does not send requests but stores them for later retrieval.
 * You can configure the mock with responses to return and/or exceptions to throw.
 *
 * @author David de Boer <david@ddeboer.nl>
 */
class Client implements HttpClient, HttpAsyncClient
{
    use HttpAsyncClientEmulator;

    /**
     * @var ResponseFactory|ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var array
     */
    private $conditionalResults = [];

    /**
     * @var RequestInterface[]
     */
    private $requests = [];

    /**
     * @var ResponseInterface[]
     */
    private $responses = [];

    /**
     * @var ResponseInterface|null
     */
    private $defaultResponse;

    /**
     * @var Exception[]
     */
    private $exceptions = [];

    /**
     * @var Exception|null
     */
    private $defaultException;

    /**
     * @param ResponseFactory|ResponseFactoryInterface|null
     */
    public function __construct($responseFactory = null)
    {
        if (!$responseFactory instanceof ResponseFactory && !$responseFactory instanceof ResponseFactoryInterface && null !== $responseFactory) {
            throw new \TypeError(
                sprintf('%s::__construct(): Argument #1 ($responseFactory) must be of type %s|%s|null, %s given', self::class, ResponseFactory::class, ResponseFactoryInterface::class, get_debug_type($responseFactory))
            );
        }

        $this->responseFactory = $responseFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * Respond with the prepared behaviour, in the following order.
     *
     * - Throw the next exception in the list and advance
     * - Return the next response in the list and advance
     * - Throw the default exception if set (forever)
     * - Return the default response if set (forever)
     * - Create a new empty response with the response factory
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        foreach ($this->conditionalResults as $result) {
            /**
             * @var RequestMatcher
             */
            $matcher = $result['matcher'];

            /**
             * @var callable
             */
            $callable = $result['callable'];

            if ($matcher->matches($request)) {
                return $callable($request);
            }
        }

        if (count($this->exceptions) > 0) {
            throw array_shift($this->exceptions);
        }

        if (count($this->responses) > 0) {
            return array_shift($this->responses);
        }

        if ($this->defaultException) {
            throw $this->defaultException;
        }

        if ($this->defaultResponse) {
            return $this->defaultResponse;
        }

        // Return success response by default
        return $this->responseFactory->createResponse();
    }

    /**
     * Adds an exception to be thrown or response to be returned if the request
     * matcher matches.
     *
     * For more complex logic, pass a callable as $result. The method is given
     * the request and MUST either return a ResponseInterface or throw an
     * exception that implements the PSR-18 / HTTPlug exception interface.
     *
     * @param ResponseInterface|Exception|ClientExceptionInterface|callable $result
     */
    public function on(RequestMatcher $requestMatcher, $result)
    {
        if (!$result instanceof ResponseInterface && !$result instanceof Exception && !$result instanceof ClientExceptionInterface && !is_callable($result)) {
            throw new \TypeError(
                sprintf('%s::on(): Argument #2 ($result) must be of type %s|%s|%s|callable, %s given', self::class, ResponseInterface::class, Exception::class, ClientExceptionInterface::class, get_debug_type($result))
            );
        }

        $callable = self::makeCallable($result);

        $this->conditionalResults[] = [
            'matcher' => $requestMatcher,
            'callable' => $callable,
        ];
    }

    /**
     * @param ResponseInterface|Exception|ClientExceptionInterface|callable $result
     *
     * @return callable
     */
    private static function makeCallable($result)
    {
        if (is_callable($result)) {
            return $result;
        }

        if ($result instanceof ResponseInterface) {
            return function () use ($result) {
                return $result;
            };
        }

        return function () use ($result) {
            throw $result;
        };
    }

    /**
     * Adds an exception that will be thrown.
     */
    public function addException(\Exception $exception)
    {
        if (!$exception instanceof Exception) {
            @trigger_error('Clients may only throw exceptions of type '.Exception::class.'. Setting an exception of class '.get_class($exception).' will not be possible anymore in the future', E_USER_DEPRECATED);
        }
        $this->exceptions[] = $exception;
    }

    /**
     * Sets the default exception to throw when the list of added exceptions and responses is exhausted.
     *
     * If both a default exception and a default response are set, the exception will be thrown.
     */
    public function setDefaultException(\Exception $defaultException = null)
    {
        if (null !== $defaultException && !$defaultException instanceof Exception) {
            @trigger_error('Clients may only throw exceptions of type '.Exception::class.'. Setting an exception of class '.get_class($defaultException).' will not be possible anymore in the future', E_USER_DEPRECATED);
        }
        $this->defaultException = $defaultException;
    }

    /**
     * Adds a response that will be returned in first in first out order.
     */
    public function addResponse(ResponseInterface $response)
    {
        $this->responses[] = $response;
    }

    /**
     * Sets the default response to be returned when the list of added exceptions and responses is exhausted.
     */
    public function setDefaultResponse(ResponseInterface $defaultResponse = null)
    {
        $this->defaultResponse = $defaultResponse;
    }

    /**
     * Returns requests that were sent.
     *
     * @return RequestInterface[]
     */
    public function getRequests()
    {
        return $this->requests;
    }

    public function getLastRequest()
    {
        return end($this->requests);
    }

    public function reset()
    {
        $this->conditionalResults = [];
        $this->responses = [];
        $this->exceptions = [];
        $this->requests = [];
        $this->setDefaultException();
        $this->setDefaultResponse();
    }
}
