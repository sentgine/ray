<?php

namespace Sentgine\Ray;

class Controller
{
    /**
     * Renders a view template with the given data.
     *
     * @param string $template The name of the view template to render.
     * @param array $data The data to pass to the view. Defaults to an empty array.
     * @return void
     */
    public function view(string $template, array $data = []): void
    {
        echo view($template, $data);
    }
}
