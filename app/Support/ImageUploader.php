<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageUploader
{
    /**
     * Store an uploaded image inside public/uploads/{directory} and return the file name.
     */
    public static function store(?UploadedFile $file, string $directory, ?string $oldFile = null): ?string
    {
        if (! $file) {
            return $oldFile;
        }

        self::delete($directory, $oldFile);

        $fileName = now()->format('YmdHis').'-'.Str::random(8).'.'.strtolower($file->getClientOriginalExtension());
        $file->move(public_path('uploads/'.$directory), $fileName);

        return $fileName;
    }

    public static function delete(string $directory, ?string $fileName): void
    {
        if (blank($fileName)) {
            return;
        }

        $path = public_path('uploads/'.$directory.'/'.$fileName);

        if (is_file($path)) {
            @unlink($path);
        }
    }
}
