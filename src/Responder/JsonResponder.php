<?php
declare(strict_types=1);

namespace Polus\Adr\Responder;

use Aura\Payload_Interface\PayloadInterface;
use Polus\Adr\PayloadStatusToHttpStatus;
use Polus\Adr\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JsonResponder implements ResponderInterface
{
    use JsonResponderTrait;

    protected $payloadStatus;

    public function __construct(PayloadStatusToHttpStatus $payloadStatus)
    {
        $this->payloadStatus = $payloadStatus;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        return $this->jsonEncode($response, $payload);
    }
}
