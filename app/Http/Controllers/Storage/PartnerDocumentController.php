<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Models\Common\File;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PartnerDocumentController extends Controller
{
    /**
     * Download partner document.
     *
     * @param string $file
     * @param Request $request
     *
     * @return  BinaryFileResponse
     */
    public function get(string $file, Request $request): BinaryFileResponse
    {
        /** @var File $document */
        $document = File::query()->where('filename', $file)->firstOrFail();

        // TODO check access rights

        return response()->file($document->path(), [
            'Cache-Control' => 'public',
            'Content-Transfer-Encoding' => 'Binary',
            'Content-Length' => $document->size,
            'Content-Type' => $document->mime,
            'Content-Disposition' => "inline; filename=\"{$document->original_filename}\"",
        ]);
    }
}
