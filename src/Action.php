<?php
declare(strict_types=1);

namespace Polus\Adr;

use Polus\Adr\Responder\JsonResponder;

class Action
{
    protected $input = Input::class;
    protected $responder = JsonResponder::class;
    protected $domain;

    public function __construct($domain = null, $input = null, $responder = null)
    {
        $actionName = $domain;
        if (is_object($actionName)) {
            $actionName = get_class($actionName);
        }
        $this->domain = $domain;

        if (!$input && class_exists($actionName . '\\Input')) {
            $this->input = $actionName . '\\Input';
        } elseif ($input) {
            $this->input = $input;
        }

        if (!$responder && class_exists($actionName . '\\Responder')) {
            $this->responder = $actionName . '\\Responder';
        } elseif ($responder) {
            $this->responder = $responder;
        }
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getResponder()
    {
        return $this->responder;
    }

    public function getDomain()
    {
        return $this->domain;
    }
}
