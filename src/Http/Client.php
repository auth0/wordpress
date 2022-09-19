<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http;

use Auth0\WordPress\Http\Message\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Client implements ClientInterface
{
    public array $options = [];

    private bool $telemetrySet = false;

    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $request->getBody()
            ->rewind();

        $destinationUri = (string) $request->getUri();
        $arguments = $this->getArguments($request);

        $this->setupTelemetry();

        $responseData = wp_remote_request($destinationUri, $arguments);

        $code = wp_remote_retrieve_response_code($responseData);
        $code = is_numeric($code) ? (int) $code : 400;

        $reason = wp_remote_retrieve_response_message($responseData);
        $headers = wp_remote_retrieve_headers($responseData);
        $headers = is_array($headers) ? $headers : iterator_to_array($headers);

        $body = wp_remote_retrieve_body($responseData);

        $response = $this->responseFactory->createResponse($code, $reason);
        $response = $response->withBody(Stream::create($body));

        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        $response->getBody()
            ->rewind();

        return $response;
    }

    /**
     * @return mixed[]
     */
    private function getArguments(RequestInterface $request): array
    {
        return array_merge($this->options, [
            'method' => $request->getMethod(),
            'httpversion' => $request->getProtocolVersion(),
            'headers' => $this->getHeaders($request),
            'body' => (string) $request->getBody(),
        ]);
    }

    /**
     * @return string[]
     */
    private function getHeaders(RequestInterface $request): array
    {
        $headers = [];

        foreach (array_keys($request->getHeaders()) as $header) {
            $headers[$header] = $request->getHeaderLine($header);
        }

        return $headers;
    }

    private function setupTelemetry(): void
    {
        if ($this->telemetrySet === true) {
            return;
        }

        require ABSPATH . WPINC . '/version.php';

        if (! isset($wp_version)) {
            try {
                $wp_version = get_site_transient('update_core')->version_checked;
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        if (! isset($wp_version)) {
            $wp_version = '0.0.0';
        }

        \Auth0\SDK\Utility\HttpTelemetry::setEnvProperty('WordPress', $wp_version);
        \Auth0\SDK\Utility\HttpTelemetry::setPackage('wp-auth0', WP_AUTH0_VERSION);

        $this->telemetrySet = true;
    }
}
