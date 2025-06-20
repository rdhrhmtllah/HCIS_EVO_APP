<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
class GetFotoController extends Controller
{
    public function show($filename)
    {
        $path = storage_path('app/public/ticket/' . $filename);

        if (!File::exists($path)) {
            abort(404);
        }

        

        $mime = File::mimeType($path);
        return response()->file($path, ['Content-Type' => $mime]);
    }
}
