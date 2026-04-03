<?php

namespace App\Support;

use App\Models\CompanySettings;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaStorageManager
{
    public static function isCloudinaryEnabled(?CompanySettings $settings = null): bool
    {
        $settings ??= CompanySettings::current();

        return ($settings->storage_provider ?? 'local') === 'cloudinary'
            && filled($settings->cloudinary_cloud_name)
            && filled($settings->cloudinary_api_key)
            && filled($settings->cloudinary_api_secret);
    }

    /**
     * @return array{path:string,url:string,provider:string}
     */
    public static function storeUploadedFile(
        UploadedFile $file,
        string $folder,
        ?string $namePrefix = null,
        ?CompanySettings $settings = null
    ): array {
        $settings ??= CompanySettings::current();

        if (self::isCloudinaryEnabled($settings)) {
            return self::storeOnCloudinary($file->getRealPath(), $folder, $namePrefix, $settings);
        }

        return self::storeLocally($file, $folder, $namePrefix);
    }

    /**
     * @return array{path:string,url:string,provider:string}
     */
    public static function storeFileFromPath(
        string $absolutePath,
        string $folder,
        ?string $namePrefix = null,
        ?CompanySettings $settings = null
    ): array {
        $settings ??= CompanySettings::current();

        if (! is_file($absolutePath)) {
            throw new \RuntimeException("File not found for upload: {$absolutePath}");
        }

        if (self::isCloudinaryEnabled($settings)) {
            return self::storeOnCloudinary($absolutePath, $folder, $namePrefix, $settings);
        }

        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION) ?: 'bin';
        $filename = self::buildFilename($namePrefix, $extension);
        $relativePath = trim($folder, '/') . '/' . $filename;
        $destination = public_path($relativePath);
        $destinationDir = dirname($destination);

        if (! is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        copy($absolutePath, $destination);

        return [
            'path' => $relativePath,
            'url' => url($relativePath),
            'provider' => 'local',
        ];
    }

    public static function publicUrl(?string $path, ?string $fallback = null, string $localPrefix = ''): string
    {
        $value = trim((string) $path);

        if ($value === '') {
            return $fallback !== null ? self::publicUrl($fallback, null, $localPrefix) : '';
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }

        if ($localPrefix !== '' && ! Str::startsWith($value, trim($localPrefix, '/') . '/')) {
            $value = trim($localPrefix, '/') . '/' . ltrim($value, '/');
        }

        return url(ltrim($value, '/'));
    }

    public static function deletePath(?string $path, ?CompanySettings $settings = null): void
    {
        $value = trim((string) $path);

        if ($value === '' || $value === 'photo_defaults.jpg') {
            return;
        }

        $settings ??= CompanySettings::current();

        if (self::isCloudinaryEnabled($settings) && Str::contains($value, 'res.cloudinary.com')) {
            $publicId = self::publicIdFromCloudinaryUrl($value);
            if ($publicId !== null) {
                try {
                    self::cloudinaryClient($settings)->uploadApi()->destroy($publicId, ['invalidate' => true]);
                } catch (\Throwable $exception) {
                    Log::warning('Cloudinary delete failed.', ['public_id' => $publicId, 'error' => $exception->getMessage()]);
                }
            }

            return;
        }

        $localPath = public_path(ltrim($value, '/'));
        if (is_file($localPath)) {
            @unlink($localPath);
        }
    }

    /**
     * @return array{path:string,url:string,provider:string}
     */
    private static function storeLocally(UploadedFile $file, string $folder, ?string $namePrefix = null): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = self::buildFilename($namePrefix, $extension);
        $relativePath = trim($folder, '/') . '/' . $filename;
        $destination = public_path($relativePath);
        $destinationDir = dirname($destination);

        if (! is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $file->move($destinationDir, $filename);

        return [
            'path' => $relativePath,
            'url' => url($relativePath),
            'provider' => 'local',
        ];
    }

    /**
     * @return array{path:string,url:string,provider:string}
     */
    private static function storeOnCloudinary(
        string $absolutePath,
        string $folder,
        ?string $namePrefix,
        CompanySettings $settings
    ): array {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION) ?: 'bin');
        $filename = self::buildFilename($namePrefix, $extension);
        $publicId = trim($settings->cloudinary_folder ?: 'purple-hr', '/')
            . '/'
            . trim($folder, '/')
            . '/'
            . pathinfo($filename, PATHINFO_FILENAME);

        $result = self::cloudinaryClient($settings)->uploadApi()->upload($absolutePath, [
            'public_id' => $publicId,
            'resource_type' => 'auto',
            'overwrite' => true,
        ]);

        $secureUrl = (string) ($result['secure_url'] ?? '');

        if ($secureUrl === '') {
            throw new \RuntimeException('Cloudinary upload succeeded without secure_url.');
        }

        return [
            'path' => $secureUrl,
            'url' => $secureUrl,
            'provider' => 'cloudinary',
        ];
    }

    private static function cloudinaryClient(CompanySettings $settings): Cloudinary
    {
        return new Cloudinary([
            'cloud' => [
                'cloud_name' => (string) $settings->cloudinary_cloud_name,
            ],
            'url' => [
                'secure' => (bool) ($settings->cloudinary_secure_delivery ?? true),
            ],
            'api' => [
                'api_key' => (string) $settings->cloudinary_api_key,
                'api_secret' => (string) $settings->cloudinary_api_secret,
            ],
        ]);
    }

    private static function buildFilename(?string $namePrefix, string $extension): string
    {
        $prefix = trim((string) $namePrefix);
        $safePrefix = $prefix !== '' ? Str::slug($prefix, '-') : 'file';

        return $safePrefix . '-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . $extension;
    }

    private static function publicIdFromCloudinaryUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if (! isset($parts['path'])) {
            return null;
        }

        $path = ltrim($parts['path'], '/');
        $segments = explode('/', $path);
        $uploadIndex = array_search('upload', $segments, true);

        if ($uploadIndex === false) {
            return null;
        }

        $afterUpload = array_slice($segments, $uploadIndex + 1);
        if (isset($afterUpload[0]) && preg_match('/^v\d+$/', $afterUpload[0])) {
            array_shift($afterUpload);
        }

        $joined = implode('/', $afterUpload);

        return preg_replace('/\.[^.\/]+$/', '', $joined) ?: null;
    }
}

