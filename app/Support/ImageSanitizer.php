<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class ImageSanitizer
{
    /**
     * Re-encode an image in place via GD to strip EXIF metadata and
     * neutralize polyglot/appended payloads. Returns the normalized
     * extension ("jpg" or "png").
     *
     * @throws ValidationException if the file is not a usable JPEG or PNG.
     */
    public static function sanitize(string $path): string
    {
        if (! function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('GD extension is required for image sanitization.');
        }

        $info = @getimagesize($path);
        if ($info === false) {
            throw ValidationException::withMessages([
                'photo' => "Le fichier n'est pas une image valide.",
            ]);
        }

        $mime = $info['mime'] ?? null;
        $data = @file_get_contents($path);
        if ($data === false) {
            throw ValidationException::withMessages([
                'photo' => "Impossible de lire le fichier.",
            ]);
        }

        $image = @imagecreatefromstring($data);
        if ($image === false) {
            throw ValidationException::withMessages([
                'photo' => "Le fichier image est corrompu.",
            ]);
        }

        try {
            if ($mime === 'image/png') {
                imagesavealpha($image, true);
                if (! imagepng($image, $path, 6)) {
                    throw ValidationException::withMessages([
                        'photo' => "Impossible de réencoder l'image.",
                    ]);
                }
                return 'png';
            }

            if ($mime === 'image/jpeg') {
                if (! imagejpeg($image, $path, 88)) {
                    throw ValidationException::withMessages([
                        'photo' => "Impossible de réencoder l'image.",
                    ]);
                }
                return 'jpg';
            }

            throw ValidationException::withMessages([
                'photo' => "Format d'image non autorisé. JPG ou PNG uniquement.",
            ]);
        } finally {
            imagedestroy($image);
        }
    }
}
