<?php

namespace PressbooksMultiInstitution\Controllers;

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
            "PressbooksMultiInstitution::{$view}",
            $data
        );
    }
}
