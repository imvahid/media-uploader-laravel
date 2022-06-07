<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Attachment\Http\Controllers',
], function () {
    Route::post('attachment_ajax_upload', 'AjaxUploadController@upload')
        ->name('attachment_upload')->middleware(config('attachment.set_middleware_to_upload_url'));

    Route::post('attachment_ajax_remove', 'AjaxUploadController@remove')
        ->name('attachment_remove')->middleware(config('attachment.set_middleware_to_remove_url'));

    Route::get('/download/attachment', 'DownloadAttachmentController@generate')
        ->name('download.attachment')
        ->middleware('signed');
});
