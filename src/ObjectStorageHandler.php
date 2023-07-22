<?php

namespace LiveControls\Storage;

use Exception;
use Illuminate\Support\Facades\Storage;
use Helvetiapps\LiveControls\Utils\Utils;

class ObjectStorageHandler
{
    protected static $disk = 'contabo';
    protected static $throwException = true;

    private static function check(): bool
    {
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
