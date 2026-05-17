<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Domain exception raised when a payment gateway is unable to fulfill a
 * request — bad credentials, gateway downtime, network failure, etc.
 * Carries the underlying gateway exception via $previous so logs keep the
 * full chain while the HTTP response stays generic.
 */
class PaymentGatewayException extends Exception
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
