<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Models\Common\File;
use App\Models\User\Helpers\Currents;
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
        $current = Currents::get($request);

        /** @var File $document */
        if ($current->isStaffAdmin()) {
            $document = File::query()->where('filename', $file)->firstOrFail();
        } else if ($current->isRepresentative() && $current->partner() !== null) {
            $document = $current->partner()->files()->where('filename', $file)->firstOrFail();
        } else {
            return abort(403);
        }

        return response()->file($document->path(), [
            'Cache-Control' => 'public',
            'Content-Transfer-Encoding' => 'Binary',
            'Content-Length' => $document->size,
            'Content-Type' => $document->mime,
            'Content-Disposition' => "inline; filename=\"{$document->original_filename}\"",
        ]);
    }
}
