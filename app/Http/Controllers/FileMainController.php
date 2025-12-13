<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileMain;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Storage;

class FileMainController extends Controller
{
    public function show(){
        $this->authorize('create', FileMain::class);

        $files = FileMain::orderBy('created_at', 'desc')->get();

        return view('files.show', ['files'=>$files]);
    }

    public function create(){
        $this->authorize('create', FileMain::class);

        return view('files.create');
    }

    public function store(Request $request){
        $this->authorize('create', FileMain::class);

        $validatedData = $request->validate([
            'file' => 'required|mimes:pdf'
           ]);
    
           $name = $request->file('file')->getClientOriginalName();
    
           //$path = $request->file('file')->store('public/files');
           $path = $request->file->storeAs('public/files', $name);

           $path = ltrim($path, 'public');
    
           $save = new FileMain;
    
           $save->uploader_id = auth()->user()->id;
           $save->name = $name;
           $save->path = $path;
           $save->type = $request->type;
           $save->is_available = 1;
           $save->save();
    
           ActivityLogController::info('MAIN-FILES', sprintf("Uploaded file with the name '%s' in %s category", $save->name, $save->type));

           return redirect()->route('filesMain.show')->with('success', 'File Has been uploaded successfully');
    
    }
    public function destroy($id){
        $file = FileMain::where('id', $id)->first();

        $this->authorize('delete', $file);

        $path = 'public' . $file->path;

        if(Storage::exists($path)){
            Storage::delete($path);
        }

        $file->delete();


        return redirect()->route('filesMain.show')->with('success', 'File Has been deleted successfully');

    }
   
}
