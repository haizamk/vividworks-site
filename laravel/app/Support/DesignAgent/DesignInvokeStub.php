<?php

namespace App\Support\DesignAgent;

interface DesignInvokeStub
{
    /**
     * Classify one invoke without opening a network connection.
     *
     * @param  array<string, mixed>  $payload
     */
    public function classify(array $payload): InvokeClassification;
}
