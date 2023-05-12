<?php

namespace Attachment\Http\Controllers;

use App\Http\Controllers\Controller;
use Attachment\Http\Requests\AttachmentRemoveRequest;
use Attachment\Http\Requests\AttachmentUploadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Plank\Mediable\Facades\ImageManipulator;
use Plank\Mediable\Facades\MediaUploader;

class AjaxUploadController extends Controller
{
    public function upload( AttachmentUploadRequest  $request )
    {
        if (
            $request->has('disk') &&
            in_array(config('filesystems.disks.' . $request->disk . '.driver'), ['ftp', 's3', 'sftp'])
        ) {
            return $this->remote($request);
        }
        return $this->local($request);
    }

    public function local($request)
    {
        if (
            $request->has('file_type')
            &&
            ($request->file_type == 'image' || $request->file_type == 'video') || ($request->file_type == 'attachment')
        ) {
            $request->has('disk') ? $disk = $request->disk : $disk = 'public';
        }

        (config('attachment.hash_file_names'))
            ? $media = MediaUploader::fromSource($request->file)
            ->toDestination($disk, jdate()->format('Y/m'))
            ->useHashForFilename()
            ->upload()
            : $media = MediaUploader::fromSource($request->file)
            ->toDestination($disk, jdate()->format('Y/m'))
            ->upload();

        $response = new \stdClass();
        $response->status = 200;
        $response->file_key = $media->getKey();
        $response->file_name = $media->basename;

        if( $request->file_type == 'image' ) {
            $response->file_url = Storage::disk($disk)->url($media->getDiskPath());
            foreach(config('attachment.image_variant_list') as $variant) {
                $currentVariant = ImageManipulator::createImageVariant($media, $variant);
                $response->{$variant} = Storage::disk($disk)->url($currentVariant->getDiskPath());
            }
        } elseif( $request->file_type == 'video' ) {
            $response->file_url = Storage::disk($disk)->url($media->getDiskPath());
        } elseif( $request->file_type == 'attachment' ) {
            $response->file_url =  ( config('filesystems.disks.' . $disk . '.visibility') == 'private' )
                ?
                URL::temporarySignedRoute(
                    'download.attachment',
                    now()->addHours(config('attachment.attachment_download_link_expire_time')),
                    ['path' => $media->getDiskPath()]
                )
                :
                Storage::disk($disk)->url($media->getDiskPath())
            ;
        }
        return response()->json($response);
    }

    public function remote($request)
    {
        (config('attachment.hash_file_names'))
            ? $media = MediaUploader::fromSource($request->file)
            ->toDestination($request->disk, jdate()->format('Y/m'))
            ->useHashForFilename()
            ->upload()
            : $media = MediaUploader::fromSource($request->file)
            ->toDestination($request->disk, jdate()->format('Y/m'))
            ->upload();

        $response = new \stdClass();
        $response->status = 200;
        $response->file_key = $media->getKey();
        $response->file_name = $media->basename;

        if (in_array(config('filesystems.disks.' . $request->disk . '.driver'), ['ftp', 'sftp'])) {
            if( $request->file_type == 'image' ) {
                $response->file_url = config('filesystems.disks.' . $request->disk . '.protocol')  . '://' . config('filesystems.disks.' . $request->disk . '.host') . '/' . config('filesystems.disks.' . $request->disk . '.base_url') . $media->getDiskPath();
                foreach(config('attachment.image_variant_list') as $variant) {
                    $currentVariant = ImageManipulator::createImageVariant($media, $variant);
                    $response->{$variant} = config('filesystems.disks.' . $request->disk . '.protocol')  . '://' . config('filesystems.disks.' . $request->disk . '.host') . '/' . config('filesystems.disks.' . $request->disk . '.base_url') . $currentVariant->getDiskPath();
                }
            } else {
                $response->file_url = config('filesystems.disks.' . $request->disk . '.protocol')  . '://' . config('filesystems.disks.' . $request->disk . '.host') . '/' .  config('filesystems.disks.' . $request->disk . '.base_url') . $media->getDiskPath();
            }
        }

        if ($request->file_type == 'image') {
            $response->file_url = 'https://' .
                config('filesystems.disks.' . $request->disk . '.bucket') .
                '.' .
                config('filesystems.disks.' . $request->disk . '.region') .
                '/' .
                $media->getDiskPath();

            foreach (config('attachment.image_variant_list') as $variant) {
                $currentVariant = ImageManipulator::createImageVariant($media, $variant);
                $response->{$variant} = 'https://' .
                    config('filesystems.disks.' . $request->disk . '.bucket') .
                    '.' .
                    config('filesystems.disks.' . $request->disk . '.region') .
                    '/' .
                    $currentVariant->getDiskPath();
            }
        } else {
            $response->file_url = 'https://' .
                config('filesystems.disks.' . $request->disk . '.bucket') .
                '.' .
                config('filesystems.disks.' . $request->disk . '.region') .
                '/' .
                $media->getDiskPath();
        }

        return response()->json($response);
    }

    public function remove( AttachmentRemoveRequest $request ) {
        $media = config('attachment.media_model')::query()->find($request->object_id);

        if($request->object_type == 'image') {
            foreach(config('attachment.image_variant_list') as $variant) {
                if($media->hasVariant($variant)) {
                    Storage::disk($media->disk)->delete($media->findVariant($variant)->getDiskPath());
                    $media->findVariant($variant)->delete();
                }
            }
            Storage::disk($media->disk)->delete($media->getDiskPath());
            $media->delete();
        } else {
            Storage::disk($media->disk)->delete($media->getDiskPath());
            $media->delete();
        }

        $response = ['message' => config('attachment.remove_file_success_message'), 'status' => 200];
        return response()->json($response);
    }
}
