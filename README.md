# Storage
 ![Release Version](https://img.shields.io/github/v/release/live-controls/storage)
 ![Packagist Version](https://img.shields.io/packagist/v/live-controls/storage?color=%23007500)
 
 Object Storage Library for live-controls

## Requirements
- PHP 8.0+
- S3 compatible Object Storage Hoster like Contabo or DigitalOcean


## Translations
None


## Installation
```
composer require live-controls/storage
```

## Setup FluentObjectStorageHandler
No Setup needed

## Setup ObjectStorageHandler
1) Add to .env:
```
OBJECTSTORAGE_ACCESS_KEY_ID=53234123 //Should be the access key id to the storage
OBJECTSTORAGE_SECRET_ACCESS_KEY=0000000 //Should be the secret access key to the storage
OBJECTSTORAGE_DEFAULT_REGION=usc1 //Should match the subdomain in endpoint or url
OBJECTSTORAGE_BUCKET=bucketName //The name of the bucket
OBJECTSTORAGE_URL=https://usc1.contabostorage.com/1234567890:bucketName //The url of the bucket
OBJECTSTORAGE_ENDPOINT=https://usc1.contabostorage.com/ //The endpoint of the bucket
OBJECTSTORAGE_USE_PATH_STYLE_ENDPOINT=true //Needs to be true to work!
```

2) Add to config/filesystems.php:
```php
'disks' => [
     ...
   'objectstorage' => [
       'driver' => 's3',
       'key' => env('OBJECTSTORAGE_ACCESS_KEY_ID'),
       'secret' => env('OBJECTSTORAGE_SECRET_ACCESS_KEY'),
       'region' => env('OBJECTSTORAGE_DEFAULT_REGION'),
       'bucket' => env('OBJECTSTORAGE_BUCKET'),
       'url' => env('OBJECTSTORAGE_URL'),
       'endpoint' => env('OBJECTSTORAGE_ENDPOINT'),
       'use_path_style_endpoint' => env('OBJECTSTORAGE_USE_PATH_STYLE_ENDPOINT', false),
       'throw' => false,
   ],
]
```

3) Publish configuration file with:
```
php artisan vendor:publish --tag="livecontrols.storage.config"
```

4) Set "storage:disk" to the name of your disk set in Step 2

## Usage
Todo
