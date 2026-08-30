<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SecureFileUpload implements ValidationRule
{
    /**
     * Allowed MIME types and their associated magic bytes & extensions.
     */
    protected array $allowedTypes = [
        'image/jpeg' => ['ext' => ['jpg', 'jpeg'], 'magic' => ["\xFF\xD8\xFF"]],
        'image/png'  => ['ext' => ['png'],          'magic' => ["\x89PNG\r\n\x1a\n"]],
        'image/webp' => ['ext' => ['webp'],         'magic' => ['RIFF', 'WEBP']],
        'application/pdf' => ['ext' => ['pdf'],     'magic' => ['%PDF']],
    ];

    protected array $customAllowedTypes;
    protected int $maxKilobytes;

    public function __construct(array $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'], int $maxKilobytes = 10240)
    {
        $this->customAllowedTypes = array_map('strtolower', $allowedExtensions);
        $this->maxKilobytes = $maxKilobytes;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            return;
        }

        // 1. Check upload error
        if (!$value->isValid()) {
            $fail("The {$attribute} failed to upload properly.");
            return;
        }

        // 2. Check file size
        if ($value->getSize() > ($this->maxKilobytes * 1024)) {
            $maxMb = round($this->maxKilobytes / 1024, 1);
            $fail("The {$attribute} may not be greater than {$maxMb} MB.");
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->customAllowedTypes, true)) {
            $allowedList = implode(', ', $this->customAllowedTypes);
            $fail("The {$attribute} must be a file of type: {$allowedList}.");
            return;
        }

        $filePath = $value->getRealPath();
        if (!$filePath || !file_exists($filePath)) {
            $fail("The uploaded file could not be read.");
            return;
        }

        // 3. Deep MIME inspection
        try {
            $detectedMime = $value->getMimeType();
        } catch (\Throwable $e) {
            $detectedMime = null;
        }

        if (!$detectedMime || !isset($this->allowedTypes[$detectedMime])) {
            $fail("The {$attribute} has an invalid or unrecognized file format (" . ($detectedMime ?? 'unknown') . ").");
            return;
        }

        // 4. Verify extension matches detected MIME
        if (!in_array($extension, $this->allowedTypes[$detectedMime]['ext'], true)) {
            $fail("The {$attribute} extension (.{$extension}) does not match its detected content type ({$detectedMime}).");
            return;
        }

        // 5. Magic Bytes Header Check
        $headerBytes = '';
        if ($handle = @fopen($filePath, 'rb')) {
            $headerBytes = (string) fread($handle, 32);
            fclose($handle);
        }

        $magicMatch = false;
        if ($detectedMime === 'application/pdf') {
            $magicMatch = str_starts_with($headerBytes, '%PDF');
        } elseif ($detectedMime === 'image/jpeg') {
            $magicMatch = str_starts_with($headerBytes, "\xFF\xD8\xFF");
        } elseif ($detectedMime === 'image/png') {
            $magicMatch = str_starts_with($headerBytes, "\x89PNG\r\n\x1a\n");
        } elseif ($detectedMime === 'image/webp') {
            $magicMatch = str_starts_with($headerBytes, 'RIFF') && str_contains(substr($headerBytes, 8, 8), 'WEBP');
        }

        if (!$magicMatch) {
            $fail("The {$attribute} failed security signature verification.");
            return;
        }

        // 6. Anti-WebShell Inspection: check file content for embedded PHP/script tags
        $contentSample = (string) @file_get_contents($filePath, false, null, 0, 4096);
        $suspiciousPatterns = ['<?php', '<?=', '<script', 'eval(', 'base64_decode('];
        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($contentSample, $pattern) !== false) {
                $fail("The {$attribute} contains disallowed or executable script content.");
                return;
            }
        }
    }
}
