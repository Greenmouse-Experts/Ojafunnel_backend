<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


trait StoreFile
{


    /**
     *
     * this trait checks if a user has already created an account
     * @param $file
     * @param $path
     * @param $fileName
     * @param int $width
     * @param int $height
     * @param bool $shouldHaveThumbnail
     * @return string
     */


    public function storeImage($file, $path, $fileName, $width = 150, $height = 150, $shouldHaveThumbnail = true)
    {


        $storageType = config('filesystems.default');
        $file_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file));
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $file_data, FILEINFO_MIME_TYPE);
        $fileExt = str_replace("image/", "", $mime_type);
        $imageName = $fileName . '.' . $fileExt;
        $filePathThumbnail = $storageType == "local" ? public_path('storage/' . $path . '/thumbnails/' . $imageName . '') : '' . $path . '/thumbnails/' . $imageName . '';
        if ($file_data != "") {
            $imageFullPath = '' . $path . '/' . $imageName . '';
            Storage::put($imageFullPath, $file_data);
            $img = Image::make($file);
            if ($shouldHaveThumbnail) {
                if ($storageType == 'local') {
                    $filePathThumbnail = public_path('storage/' . $path . '/thumbnails/' . $imageName . '');
                    if (!file_exists('storage/' . $path . '/thumbnails')) {
                        mkdir('storage/' . $path . '/thumbnails', 666, true);
                    }
                    $img->resize($width, $height, function ($const) {
                        $const->aspectRatio();
                    })->save($filePathThumbnail);
                } else {
                    $encodedThumbnail = $img->resize($width, $height)->encode()->encoded;
                    Storage::put($filePathThumbnail, $encodedThumbnail);
                }
            }
        }
        return $imageName;
    }

    public function getImage($path)
    {
        try {
            $contents = Storage::get($path);
            $mediaType = "data:image/";
            $base64EncodedString = base64_encode($contents);
            $file_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64EncodedString));
            $f = finfo_open();
            $mime_type = finfo_buffer($f, $file_data, FILEINFO_MIME_TYPE);
            $fileExt = str_replace("image/", "", $mime_type);
            return '' . $mediaType . '' . $fileExt . ';base64,' . $base64EncodedString . '';
        } catch (\Exception $exception) {
            return '';
        }
    }
}
