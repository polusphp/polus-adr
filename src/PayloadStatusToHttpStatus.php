<?php

namespace Polus\Adr;

use Aura\Payload_Interface\PayloadInterface;

class PayloadStatusToHttpStatus
{
    protected $payloadToHttp = [
        'FOUND' => 200,
        'SUCCESS' => 200,
        'AUTHORIZED' => 200,
        'AUTHENTICATED' => 200,
        'CREATED' => 201,
        'ACCEPTED' => 202,
        'PROCESSING' => 203,
        'DELETED' => 204,
        'VALID' => 204,
        'UPDATED' => 303,
        'FAILURE' => 400,
        'NOT_AUTHENTICATED' => 401,
        'NOT_AUTHORIZED' => 403,
        'NOT_FOUND' => 404,
        'NOT_VALID' => 422,
        'ERROR' => 500,
        'NOT_ACCEPTED' => 500,
        'NOT_CREATED' => 500,
        'NOT_DELETED' => 500,
        'NOT_UPDATED' => 500,
    ];

    protected $payloadToJsend = [
        'FOUND' => 'success',
        'SUCCESS' => 'success',
        'AUTHORIZED' => 'success',
        'AUTHENTICATED' => 'success',
        'CREATED' => 'success',
        'ACCEPTED' => 'success',
        'PROCESSING' => 'success',
        'DELETED' => 'success',
        'VALID' => 'success',
        'UPDATED' => 'success',
        'FAILURE' => 'fail',
        'NOT_AUTHENTICATED' => 'fail',
        'NOT_AUTHORIZED' => 'fail',
        'NOT_FOUND' => 'fail',
        'NOT_VALID' => 'fail',
        'ERROR' => 'error',
        'NOT_ACCEPTED' => 'error',
        'NOT_CREATED' => 'error',
        'NOT_DELETED' => 'error',
        'NOT_UPDATED' => 'error',
    ];

    public function getHttpStatus(PayloadInterface $payload)
    {
        if (isset($this->payloadToHttp[$payload->getStatus()])) {
            return $this->payloadToHttp[$payload->getStatus()];
        }
        return 500;
    }

    public function getJsendStatus(PayloadInterface $payload)
    {
        if (isset($this->payloadToJsend[$payload->getStatus()])) {
            return $this->payloadToJsend[$payload->getStatus()];
        }
        return 'error';
    }
}
