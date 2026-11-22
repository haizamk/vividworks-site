<?php

namespace App\Support\DesignAgent;

/**
 * Default F1 mock. It does not call W2 and it does not claim the bytes were withheld.
 */
class UnknownDesignInvokeStub implements DesignInvokeStub
{
    public function classify(array $payload): InvokeClassification
    {
        return InvokeClassification::unknown();
    }
}
