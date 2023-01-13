<?php

namespace App\Helpers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;

class Fiscal
{
    /**
     * Get fiscal document file path and name.
     *
     * @param string $gate
     * @param string $fiscal
     *
     * @return  string
     */
    public static function path(string $gate, string $fiscal): string
    {
        return $gate . DIRECTORY_SEPARATOR . substr($fiscal, 0, 2) . DIRECTORY_SEPARATOR . $fiscal . '.txt';
    }

    /**
     * Get fiscal document content.
     *
     * @param string $gate
     * @param string $fiscal
     *
     * @return  string
     *
     * @throws FileNotFoundException
     */
    public static function get(string $gate, string $fiscal): string
    {
        return Storage::disk('fiscal')->get(self::path($gate, $fiscal));
    }

    /**
     * Write fiscal document.
     *
     * @param string $gate
     * @param string $fiscal
     * @param string $content
     *
     * @return  void
     */
    public static function put(string $gate, string $fiscal, string $content): void
    {
        Storage::disk('fiscal')->put(self::path($gate, $fiscal), $content);
    }
}
