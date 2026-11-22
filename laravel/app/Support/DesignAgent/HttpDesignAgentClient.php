<?php

namespace App\Support\DesignAgent;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * POST the design request to the Design Agent webhook.
 *
 * The header name is configuration. The workflow only records that the
 * webhook uses header auth; it does not store the header name in the node.
 */
class HttpDesignAgentClient implements DesignAgentClient
{
    public function send(array $payload): DesignAgentResponse
    {
        $url = (string) config('services.vividai.design_agent_url');
        if ($url === '') {
            throw new RuntimeException('Design Agent URL is not configured.');
        }

        $request = Http::acceptJson()
            ->timeout((int) config('services.vividai.design_agent_timeout', 120));

        $header = (string) config('services.vividai.design_agent_header');
        $token = (string) config('services.vividai.design_agent_token');
        if ($header !== '' && $token !== '') {
            $request = $request->withHeader($header, $token);
        }

        try {
            $response = $request->post($url, $payload);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Design Agent could not be reached.', 0, $exception);
        }

        return new DesignAgentResponse($response->status(), $response->json());
    }
}
