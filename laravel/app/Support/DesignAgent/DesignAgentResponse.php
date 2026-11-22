<?php

namespace App\Support\DesignAgent;

class DesignAgentResponse
{
    public function __construct(
        public int $httpStatus,
        public mixed $body,
    ) {}
}
