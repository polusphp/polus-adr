<?php

namespace Polus\Adr;

interface ActionInterface
{
    public function getInput();
    public function getResponder();
    public function getDomain();
}