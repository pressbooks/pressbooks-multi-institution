<?php

namespace PressbooksMultiInstitution\Controllers;

class InstitutionsController extends BaseController
{
    public function index(): string
    {
        return $this->renderView('institutions.index', [
            'message' => __('Here is the institution list page.', 'pressbooks-multi-institution')
        ]);
    }
}
