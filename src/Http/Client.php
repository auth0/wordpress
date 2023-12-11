<?php

declare(strict_types=1);

namespace Auth0\WordPress\Http;

use Auth0\SDK\Utility\HttpTelemetry;
use Auth0\WordPress\Http\Message\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestInterface, ResponseFactoryInterface, ResponseInterface};
use Throwable;

use function is_array;

final class Client implements ClientInterface
{
    private bool $telemetrySet = false;

    public array $options = [];

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

        // error_log($arguments['method'] . ' -> ' . $destinationUri);
        // error_log(str_repeat(' ', strlen($arguments['method']) + 4) . json_encode($responseData));

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
     * @param RequestInterface $request
     *
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
     * @param RequestInterface $request
     *
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

    /**
     * Configure the HTTP telemetry for Auth0 API calls.
     *
     * @psalm-suppress UnresolvableInclude,UndefinedConstant
     */
    private function setupTelemetry(): void
    {
        $wp_version = '5.0.0';

        if (! $this->telemetrySet) {
            return;
        }

        require ABSPATH . WPINC . '/version.php';

        /** @var string $wp_version */
        if ('' === $wp_version) {
            try {
                $core = get_site_transient('update_core');

                /** @var object $core */
                if (property_exists($core, 'version_checked')) {
                    $wp_version = $core->version_checked;
                }
            } catch (Throwable) {
                // Silently ignore if unavailable.
            }
        }

        if (! isset($wp_version) || false === $wp_version) {
            $wp_version = '0.0.0';
        }

        HttpTelemetry::setEnvProperty('WordPress', $wp_version);
        HttpTelemetry::setPackage('wordpress', WP_AUTH0_VERSION);

        $this->telemetrySet = true;
    }
}
