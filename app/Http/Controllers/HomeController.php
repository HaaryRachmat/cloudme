<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use SoareCostin\FileVault\Facades\FileVault;
use Str;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $files = Storage::files('files/' . auth()->user()->id);

        return view('home', compact('files'));
    }

    public function store(Request $request)
    {
        if ($request->hasFile('userFile') && $request->file('userFile')->isValid()) {
            $filename = Storage::putFile('files/' . auth()->user()->id, $request->file('userFile'));

            // Check to see if we have a valid file uploaded
            if ($filename) {
                FileVault::encrypt($filename);
            }
        }

        return redirect()->route('home')->with('message', 'Upload complete');
    }
    public function downloadFile($filename)
    {
        // Basic validation to check if the file exists and is in the user directory
        if (!Storage::has('files/' . auth()->user()->id . '/' . $filename)) {
            abort(404);
        }

        return response()->streamDownload(function () use ($filename) {
            FileVault::streamDecrypt('files/' . auth()->user()->id . '/' . $filename);
        }, Str::replaceLast('.enc', '', $filename));
    }
}

