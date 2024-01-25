<?php

namespace PressbooksPluginScaffold\Controllers;

use Pressbooks\Container;

class BaseController
{
    protected mixed $view;

    public function __construct()
    {
        $this->view = Container::get('Blade');
    }

    protected function renderView(string $view, array $data = []): string
    {
        return $this->view->render(
            "PressbooksPluginScaffold::{$view}",
            $data
        );
    }
}
