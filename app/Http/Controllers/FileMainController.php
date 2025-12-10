<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FileMain;
use App\Http\Controllers\ActivityLogController;

class FileMainController extends Controller
{
    public function show(){
        $files = FileMain::orderBy('created_at', 'desc')->get();
        return view('files.show', ['files'=>$files]);
    }
    public function create(){
        return view('files.create');
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'file' => 'required|mimes:pdf'
           ]);
    
           $name = $request->file('file')->getClientOriginalName();
    
           //$path = $request->file('file')->store('public/files');
           $request->file('file')->move(public_path('files'), $name);
    
           $save = new FileMain;
    
           $save->uploader_id = auth()->user()->id;
           $save->name = $name;
           $save->path = '/files/'.$name;
           $save->type = $request->type;
           $save->is_available = 1;
           $save->save();
    
           ActivityLogController::info('MAIN-FILES', sprintf("Uploaded file with the name '%s' in %s category", $save->name, $save->type));

           return redirect()->route('filesMain.show')->with('success', 'File Has been uploaded successfully');
    
    }
    public function destroy($id){
        $file = FileMain::where('id', $id)->first();
        if(file_exists(public_path($file->path))){
            unlink(public_path($file->path));
        }
        $file->delete();


        return redirect()->route('filesMain.show')->with('success', 'File Has been deleted successfully');

    }
   
}
