<?php

namespace Attachment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadAttachmentController extends Controller
{
    public function generate(Request $request)
    {
        return Storage::disk(config('attachment.default_disk'))->download($request->path);
    }
}
