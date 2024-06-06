<?php

namespace App\Models;

use DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cdn;
use URL;
trait ModelTrait
{
    protected function get_domain_cdn(){

        $cdn = Cdn::first();
        if($cdn->status == 1 && $cdn->domain != ''){
            return $cdn->domain;
        }
        return URL::to('/');
    }

    public function uploadImageToDisk($value, $attribute_name, $disk, $destination_path, $add_to_attributes = true){
        // if the image was erased
        if($add_to_attributes){
            if ($value==null) {
                // delete the image from disk
                //\Storage::disk($disk)->delete($this->{$attribute_name});

                // set null in the database column
                $this->attributes[$attribute_name] = null;
            }
        }

        // if a base64 was sent, store it in the db
        if (starts_with($value, 'data:image'))
        {

            // 0. Make the image

            $image = explode(";", $value)[0];
            $type = explode("/", $image)[1];
            $random = rand(1000, 9999);

            if($type == 'webp'){
                $image = imagecreatefromwebp($value);
                $image = \Image::make($image)->encode('webp', 90);
                // 1. Generate a filename.
                $filename = time() . $random . '.webp';

            }else if(in_array($type, ['png','jpg','jpeg'])){
                $image = \Image::make($value)->encode($type, 90);
                // 1. Generate a filename.
                $filename = time() . $random.'.'.$type;

            }else{
                // 1. Generate a filename.
                $filename = time() . $random.'.'.$type;
                $value = explode(";base64,", $value)[1];
            }

            if(in_array($type, ['png','jpg','jpeg','webp'])){
                // 2. Store the image on disk.
                \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());

                // 3. Delete the previous image, if there was one.
                //\Storage::disk($disk)->delete($this->{$attribute_name});
            }else{
                file_put_contents($destination_path.'/'.$filename, base64_decode($value));
            }


            // 4. Save the public path to the database
            // but first, remove "public/" from the path, since we're pointing to it
            // from the root folder; that way, what gets saved in the db
            // is the public URL (everything that comes after the domain name)
            //$public_destination_path = Str::replaceFirst('public/', '', $destination_path);

            if($add_to_attributes){
                $this->attributes[$attribute_name] = $destination_path.'/'.$filename;
            }else{
                return $destination_path.'/'.$filename;
            }

        }
    }


    public function uploadImagePngToDisk($value, $attribute_name, $disk, $destination_path, $add_to_attributes = true){
        // if the image was erased
        if($add_to_attributes){
            if ($value==null) {
                // delete the image from disk
                //\Storage::disk($disk)->delete($this->{$attribute_name});

                // set null in the database column
                $this->attributes[$attribute_name] = null;
            }
        }

        // if a base64 was sent, store it in the db
        if (starts_with($value, 'data:image'))
        {
            // 0. Make the image
            $image = \Image::make($value)->encode('png', 90);

            // 1. Generate a filename.
            //$filename = md5($value.time()).'.png';
            $random = rand(1000, 9999);
            $filename = time() . $random . '.png';

            // 2. Store the image on disk.
            \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());

            // 3. Delete the previous image, if there was one.
            //\Storage::disk($disk)->delete($this->{$attribute_name});

            // 4. Save the public path to the database
            // but first, remove "public/" from the path, since we're pointing to it
            // from the root folder; that way, what gets saved in the db
            // is the public URL (everything that comes after the domain name)
            //$public_destination_path = Str::replaceFirst('public/', '', $destination_path);

            if($add_to_attributes){
                $this->attributes[$attribute_name] = $destination_path.'/'.$filename;
            }else{
                return $destination_path.'/'.$filename;
            }
        }
    }

    public function uploadImageTinyPng($value, $attribute_name, $disk, $tmp_path, $destination_path, $add_to_attributes = true){
        // if the image was erased
        if($add_to_attributes){
            if ($value==null) {
                // delete the image from disk
                //\Storage::disk($disk)->delete($this->{$attribute_name});

                // set null in the database column
                $this->attributes[$attribute_name] = null;
            }
        }

        // if a base64 was sent, store it in the db
        if (starts_with($value, 'data:image'))
        {
            // 0. Make the image
            $image = \Image::make($value)->encode('png', 90);

            // 1. Generate a filename.
            //$filename = md5($value.time()).'.png';
            $random = rand(1000, 9999);
            $filename = time() . $random . '.png';

            // 2. Store the image on disk.
            \Storage::disk($disk)->put($tmp_path.'/'.$filename, $image->stream());

            // 3. Delete the previous image, if there was one.
            //\Storage::disk($disk)->delete($this->{$attribute_name});

            // 4. Save the public path to the database
            // but first, remove "public/" from the path, since we're pointing to it
            // from the root folder; that way, what gets saved in the db
            // is the public URL (everything that comes after the domain name)
            //$public_destination_path = Str::replaceFirst('public/', '', $destination_path);

            \Tinify\setKey(env("TINY_KEY"));
            $source = \Tinify\fromFile($tmp_path.'/'.$filename);
            $source->toFile($destination_path.'/'.$filename);

            if($add_to_attributes){
                $this->attributes[$attribute_name] = $destination_path.'/'.$filename;
            }else{
                return $destination_path.'/'.$filename;
            }
        }
    }
    public function uploadImageTiny($value, $attribute_name, $disk, $tmp_path, $destination_path, $add_to_attributes = true){
        // if the image was erased
        if($add_to_attributes){
            if ($value==null) {
                // delete the image from disk
                //\Storage::disk($disk)->delete($this->{$attribute_name});

                // set null in the database column
                $this->attributes[$attribute_name] = null;
            }
        }

        // if a base64 was sent, store it in the db
        if (starts_with($value, 'data:image'))
        {
            // 0. Make the image

            $image = explode(";", $value)[0];
            $type = explode("/", $image)[1];

            if($type == 'webp'){
                $image = imagecreatefromwebp($value);
                $image = \Image::make($image)->encode('webp', 90);
                // 1. Generate a filename.
                $filename = md5($value.time()).'.webp';

            }else if(in_array($type, ['png','jpg','jpeg'])){
                $image = \Image::make($value)->encode($type, 90);
                // 1. Generate a filename.
                $filename = md5($value.time()).'.'.$type;
            }else{
                // 1. Generate a filename.
                $filename = md5($value.time()).'.'.$type;
                $value = explode(";base64,", $value)[1];
            }

            if(in_array($type, ['png','jpg','jpeg','webp'])){
                // 2. Store the image on disk.
                \Storage::disk($disk)->put($tmp_path.'/'.$filename, $image->stream());

                // 3. Delete the previous image, if there was one.
                //\Storage::disk($disk)->delete($this->{$attribute_name});
            }else{
                file_put_contents($tmp_path.'/'.$filename, base64_decode($value));
            }

            // 4. Save the public path to the database
            // but first, remove "public/" from the path, since we're pointing to it
            // from the root folder; that way, what gets saved in the db
            // is the public URL (everything that comes after the domain name)
            //$public_destination_path = Str::replaceFirst('public/', '', $destination_path);

            \Tinify\setKey(env("TINY_KEY"));
            $source = \Tinify\fromFile($tmp_path.'/'.$filename);
            $source->toFile($destination_path.'/'.$filename);

            if($add_to_attributes){
                $this->attributes[$attribute_name] = $destination_path.'/'.$filename;
            }else{
                return $destination_path.'/'.$filename;
            }
        }
    }
}
