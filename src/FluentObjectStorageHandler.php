<?php

namespace LiveControls\Storage;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use LiveControls\Storage\Models\DbDisk;
use LiveControls\Utils\Utils;

class FluentObjectStorageHandler
{
    public $disk;

    public static function disk(array|string|int|DbDisk|Model $config = 's3'): FluentObjectStorageHandler
    {
        if(is_integer($config)){
            $config = DbDisk::find($config,[
                'driver',
                'root',
                'throw',
                'key',
                'secret',
                'region',
                'bucket',
                'url',
                'endpoint',
                'use_path_style_endpoint',
                'visibility'
            ]);
            if(is_null($config)){
                throw new Exception('Invalid DbDisk with Id '.$config);
            }
            $config = $config->toArray();
        }elseif($config instanceof Model){
            $config = $config->only([
                'driver',
                'root',
                'throw',
                'key',
                'secret',
                'region',
                'bucket',
                'url',
                'endpoint',
                'use_path_style_endpoint',
                'visibility'
            ]);
        }

        $osh = new FluentObjectStorageHandler();
        $osh->disk = is_array($config) ? Storage::build($config) : $osh->disk = Storage::disk($config);
        return $osh;
    }

    public function exists($path): bool
    {
        return $this->disk->exists($path);
    }

    public function put($folder, $content, $fileName = "", bool $private = true): bool|string
    {
        if(Utils::isNullOrEmpty($fileName)){
            return $this->disk->put($folder, $content, ($private ? 'private' : 'public'));
        }
        return $this->disk->putFileAs(
            $folder, $content, $fileName
        );
    }

    public function putImage($folder, $content, $fileName = "", $width = null, $height = null, bool $private = true): bool|string
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

    public function url($path): string|null
    {
        return $this->disk->url($path);
    }

    public function temporaryUrl($path, Carbon $expire, array $parameters = [])
    {
        return $this->disk->temporaryUrl(
            $path,
            $expire,
            $parameters
        );
    }

    public function get($path): string|null
    {
        return $this->disk->get($path);
    }

    public function delete(string|array $paths): bool
    {
        return $this->disk->delete($paths);
    }

    public function download($path, $name = null, $headers = [])
    {
        return $this->disk->download($path, $name, $headers);
    }

    public function baseImage($path): string|null
    {
        $content = $this->get($path);
        if(is_null($content)){
            return null;
        }
        return 'data:image/jpeg;base64,'.base64_encode($content);
    }
}