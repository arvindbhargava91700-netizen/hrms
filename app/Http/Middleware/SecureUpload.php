<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureUpload
{
    /**
     * Allowed file extensions.
     */
    protected array $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'csv',
        'txt',
        'zip',
    ];

    /**
     * Dangerous extensions that should NEVER be uploaded.
     */
    protected array $blockedExtensions = [
        'php',
        'php3',
        'php4',
        'php5',
        'php7',
        'php8',
        'phtml',
        'phar',
        'cgi',
        'pl',
        'py',
        'rb',
        'sh',
        'bash',
        'js',
        'mjs',
        'html',
        'htm',
        'svg',
        'exe',
        'com',
        'bat',
        'cmd',
        'dll',
        'so',
        'msi',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Check uploaded files
        |--------------------------------------------------------------------------
        */

        foreach ($request->allFiles() as $files) {

            // Handle nested file arrays
            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {

                if (!$file || !$file->isValid()) {
                    continue;
                }

                $extension = strtolower($file->getClientOriginalExtension());

                /*
                |--------------------------------------------------------------------------
                | Block dangerous extensions
                |--------------------------------------------------------------------------
                */

                if (in_array($extension, $this->blockedExtensions, true)) {
                    return response()->json([
                        'message' => 'This file type is not allowed.'
                    ], 422);
                }

                /*
                |--------------------------------------------------------------------------
                | Allow only known extensions
                |--------------------------------------------------------------------------
                */

                if (!in_array($extension, $this->allowedExtensions, true)) {
                    return response()->json([
                        'message' => 'The uploaded file type is not allowed.'
                    ], 422);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Check text inputs for script injection
        |--------------------------------------------------------------------------
        */

        $this->checkInputForScripts($request->all());

        return $next($request);
    }

    protected function checkInputForScripts($data): void
    {
        foreach ($data as $key => $value) {

            if (is_array($value)) {
                $this->checkInputForScripts($value);
                continue;
            }

            if (!is_string($value)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Detect common XSS/script payloads
            |--------------------------------------------------------------------------
            */

            $patterns = [
                '/<\s*script\b/i',
                '/<\s*\/\s*script\s*>/i',
                '/javascript\s*:/i',
                '/vbscript\s*:/i',
                '/<\s*iframe\b/i',
                '/<\s*object\b/i',
                '/<\s*embed\b/i',
                '/<\s*applet\b/i',
                '/onerror\s*=/i',
                '/onload\s*=/i',
                '/onclick\s*=/i',
                '/onmouseover\s*=/i',
                '/onfocus\s*=/i',
                '/onmouseenter\s*=/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    abort(422, 'Invalid input detected.');
                }
            }
        }
    }
}