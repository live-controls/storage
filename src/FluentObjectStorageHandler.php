<?php

namespace LiveControls\Storage;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LiveControls\Storage\Models\DbDisk;
use LiveControls\Utils\Utils;

class FluentObjectStorageHandler
{
    public $disk;

    /**
     * Mirrors the content from one disk to another
     *
     * @param array|string|integer|DbDisk|Model $configFrom The configuration form the disk the content comes from
     * @param array|string|integer|DbDisk|Model $configTo THe configuration from the disk the content goes to
     * @param string $directory The directory the search should start, if recursive is true this will be the only directory mirrored
     * @param boolean $recursive Should the search be recursive?
     * @param boolean $log If set to true, Log::*() will be called
     * @return int Amount of files changed
     */
    public static function mirror(array|string|int|DbDisk|Model $configFrom, array|string|int|DbDisk|Model $configTo, string $directory = "/", bool $recursive = true, bool $log = false): int
    {
        $filesMirrored = 0;
        $diskFrom = static::disk($configFrom)->disk;
        $diskTo = static::disk($configTo)->disk;
        foreach ($diskFrom->files($directory) as $file) {
            if($diskTo->exists($file)){
                $sizeTo = $diskTo->size($file);
                $sizeFrom = $diskFrom->size($file);
                if ($sizeTo != $sizeFrom) {
                    $diskTo->delete($file);
                    Log::debug("Removed file \"".$file."\" because its size (".$sizeFrom."/".$sizeTo.") is different");
                }
            }

            if (!$diskTo->exists($file)) {
                $diskTo->put($file, $diskFrom->readStream($file), $diskFrom->getVisibility($file));
                $filesMirrored++;
                Log::debug("Mirrored file \"".$file."\"");
            }
        }
        if($recursive){
            foreach ($diskFrom->directories($directory) as $dir) {
                $filesMirrored += static::mirror($configFrom, $configTo, $dir, $recursive, $log);
                Log::debug("Start recursive mirroring for directory \"".$dir."\"");
            }
        }
        return $filesMirrored;
    }

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

        $config['directory_separator'] = config('livecontrols_storage.directory_separator', '/');

        $osh = new FluentObjectStorageHandler();
        $osh->disk = is_array($config) ? Storage::build($config) : Storage::disk($config);
        return $osh;
    }

    public function exists($path): bool
    {
        return $this->disk->exists($path);
    }

    /**
     * Puts the content to the storage server from an Url
     *
     * @param string $folder The folder the file should be saved to
     * @param string $url The url the file comes from
     * @param ?string $fileName The filename, if set to null the filename will be taken from the url
     * @param boolean $private If file access should be private or public
     * @return boolean|string
     */
    public function putFromUrl(string $folder, string $url, ?string $fileName = "", bool $private = true): bool|string
    {
        $content = file_get_contents($url);
        if(is_null($fileName)){
            $fileName = strtok(basename($url, "?" . PATHINFO_FILENAME), '?');
        }
        return static::put($folder, $content, $fileName, $private);
    }

    public function put($folder, $content, $fileName = "", bool $private = true): bool|string
    {
        if(Utils::isNullOrEmpty($fileName)){
            return $this->disk->put($folder, $content, ($private ? 'private' : 'public'));
        }
        return $this->disk->putFileAs(
            $folder, $content, $fileName, ($private ? 'private' : 'public')
        );
    }

    public function putFile($folder, $content, bool $private = true): string|false
    {
        return $this->disk->putFile($folder, $content, ($private ? 'private' : 'public'));
    }
    
    public function putImageFromUrl(string $folder, string $url, ?string $fileName = "", ?int $width = null, ?int $height = null, bool $private = true): bool|string
    {
        $content = $url;
        if(is_null($fileName)){
            $fileName = strtok(basename($url, "?" . PATHINFO_FILENAME), '?');
        }
        return static::putImage($folder, $content, $fileName, $width, $height, $private);
    }

    public function putImage($folder, $content, $fileName = "", ?int $width = null, ?int $height = null, bool $private = true): bool|string
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

    public function deleteDirectory(string|array $directories, bool $continue = true): bool
    {
        $didFail = false;
        if(!is_array($directories))
        {
            $directories = [$directories];
        }
        foreach($directories as $directory)
        {
            if(!$this->disk->deleteDirectory($directory)){
                $didFail = true;
                if($continue === false){
                    return false;
                }
            }
        }
        return !$didFail;
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
