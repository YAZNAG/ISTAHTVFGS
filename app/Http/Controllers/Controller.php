<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function pdfHeaderBase64(): ?string
    {
        $path = public_path('images/pdf-header.jpg');
        if (! file_exists($path)) {
            return null;
        }
        $data = file_get_contents($path);
        if ($data === false) {
            return null;
        }
        return 'data:image/jpeg;base64,' . base64_encode($data);
    }
}
