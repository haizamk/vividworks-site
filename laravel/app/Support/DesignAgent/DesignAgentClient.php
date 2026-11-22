<?php

namespace App\Support\DesignAgent;

interface DesignAgentClient
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function send(array $payload): DesignAgentResponse;
}
