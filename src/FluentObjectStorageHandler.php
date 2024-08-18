<?php

namespace LiveControls\Storage;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use LiveControls\Utils\Utils;

class FluentObjectStorageHandler
{
    protected $disk;

    protected static function disk(array|string $config = 's3'): FluentObjectStorageHandler
    {
        $osh = new FluentObjectStorageHandler();
        $osh->disk = is_array($config) ? Storage::build($config) : $osh->disk = Storage::disk($config);
        return $osh;
    }

    protected function exists($path): bool
    {
        return $this->disk->exists($path);
    }

    protected function put($folder, $content, $fileName = "", bool $private = true): bool|string
    {
        if(Utils::isNullOrEmpty($fileName)){
            return $this->disk->put($folder, $content, ($private ? 'private' : 'public'));
        }
        return $this->disk->putFileAs(
            $folder, $content, $fileName
        );
    }

    protected function putImage($folder, $content, $fileName = "", $width = null, $height = null, bool $private = true): bool|string
    {
        if(!is_null($width) && !is_null($height)){
            //This will most likely only work with a file uploaded with livewire, not sure if this would work with plain laravel
            $fContent = file_get_contents($content->getRealPath());
            $img = imagecreatefromstring($fContent);
            $img = imagescale($img, $width, $height);
            imagejpeg($img, $content->getRealPath());
        }
        return Utils::isNullOrEmpty($fileName) ? $this->disk->put($folder, $content, ($private ? 'private' : 'public')) : $this->disk->putFileAs(
            $folder, $content, $fileName, ($private ? 'private' : 'public')
        );
    }

    protected function url($path): string|null
    {
        return $this->disk->url($path);
    }

    protected function temporaryUrl($path, Carbon $expire, array $parameters = [])
    {
        return $this->disk->temporaryUrl(
            $path,
            $expire,
            $parameters
        );
    }

    protected function get($path): string|null
    {
        return $this->disk->get($path);
    }

    protected function delete(string|array $paths): bool
    {
        return $this->disk->delete($paths);
    }

    protected function download($path, $name = null, $headers = [])
    {
        return $this->disk->download($path, $name, $headers);
    }

    protected function baseImage($path): string|null
    {
        $content = $this->get($path);
        if(is_null($content)){
            return null;
        }
        return 'data:image/jpeg;base64,'.base64_encode($content);
    }
}