<?php

namespace PressbooksMultiInstitution\Controllers;

class BaseController
{
    protected mixed $view;

    public function __construct()
    {
        $this->view = app('Blade');
    }

    protected function renderView(string $view, array $data = []): string
    {
        return $this->view->render(
            "PressbooksMultiInstitution::{$view}",
            $data
        );
    }
}
