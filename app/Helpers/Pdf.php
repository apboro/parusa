<?php

namespace App\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf
{
    public static function generate($html, $paperSize, $orientation): ?string
    {
        $options = new Options();
//        $options->setFontDir(resource_path('fonts'));
//        $options->setFontCache(storage_path('framework/dompdf/cache'));

        $generator = new Dompdf($options);
        $generator->setPaper($paperSize, $orientation);

        $generator->loadHtml($html);
        $generator->render();

        return $generator->output();
    }
}
