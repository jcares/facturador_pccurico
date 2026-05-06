<?php

namespace Core;

abstract class Controller
{
    protected function view($name, $data = [])
    {
        View::render($name, $data);
    }

    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
}
