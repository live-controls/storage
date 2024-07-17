<?php

namespace LiveControls\Storage;

use Exception;
use Illuminate\Support\Facades\Storage;
use LiveControls\Utils\Utils;

class ObjectStorageHandler
{
    protected static $disk;
    protected static $throwException = true;

    private static function check(): bool
    {
        if(is_null(static::$disk)){
            static::$disk = config('livecontrols_storage.storage_disk',null);
            if(is_null(static::$disk)){
                throw new Exception('You need to set a disk inside config/livecontrols_storage.php or publish the configuration file!');
            }
        }
        $drvr = config('filesystems.disks.'.static::$disk.'.driver');
        if(is_null($drvr)){
            if(static::$throwException){
                throw new Exception('Disk "'.static::$disk.'" not found! Did you set in in the filesystems configuration file?');
            }
            return false;
        }
        if($drvr != "s3"){
            if(static::$throwException){
                throw new Exception('Driver for Disk "'.static::$disk.'" needs to be "S3" but is "'.$drvr.'"! Did you set in in the filesystems configuration file?"');
            }
            return false;
        }
        return true;
    }

    public static function exists($path): bool
    {
        static::check();
        return Storage::disk(static::$disk)->exists($path);
    }

    public static function put($folder, $content, $fileName = "")
    {
        static::check();
        if(Utils::isNullOrEmpty($fileName)){
            return Storage::disk(static::$disk)->put($folder, $content);
        }
        return Storage::disk(static::$disk)->putFileAs(
            $folder, $content, $fileName
        );
    }

    public static function putImage($folder, $content, $fileName = "", $width = null, $height = null)
    {
        static::check();
        if(!is_null($width) && !is_null($height)){
            $fContent = file_get_contents($content->getRealPath());
            $img = imagecreatefromstring($fContent);
            $img = imagescale($img, $width, $height);
            imagejpeg($img, $content->getRealPath());
        }
        return Utils::isNullOrEmpty($fileName) ? Storage::disk(static::$disk)->put($folder, $content) : Storage::disk(static::$disk)->putFileAs(
            $folder, $content, $fileName
        );
    }

    public static function get($path): string|null
    {
        static::check();
        return Storage::disk(static::$disk)->get($path);
    }

    public static function delete(string|array $paths): bool
    {
        static::check();
        return Storage::disk(static::$disk)->delete($paths);
    }

    public static function download($path, $name = null, $headers = [])
    {
        static::check();
        return Storage::disk(static::$disk)->download($path, $name, $headers);
    }

    public static function baseImage($path): string|null
    {
        static::check();
        $content = static::get($path);
        if(is_null($content)){
            return null;
        }
        return 'data:image/jpeg;base64,'.base64_encode($content);
    }
}
