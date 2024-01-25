<?php

namespace PressbooksPluginScaffold\Controllers;

class DemoController extends BaseController
{
    public function index(): string
    {
        return $this->renderView('demo.template');
    }
}
