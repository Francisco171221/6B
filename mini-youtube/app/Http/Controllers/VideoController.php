<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//Muestra los registros de la tabla
       $vs_videos = Video::where('status', '=', 1)
       ->join('users', 'users.id', '=', 'videos.user_id')
       ->select('users.name', 'users.email', 'videos.*')
       ->get();
       $videos = $this->cargarDT($vs_videos);
       return view('video.index')->with('videos', $videos);


    }

    public function cargarDT($consulta)
   {
       $videos = [];
       foreach ($consulta as $key => $value) {
           $ruta = "eliminar" . $value['id'];
           $eliminar = route('delete-video', $value['id']);
           $actualizar = route('videos.edit', $value['id']);
           $acciones = '
          <div class="btn-acciones">
              <div class="btn-circle">
                  <a href="' . $actualizar . '" role="button" class="btn btn-success" title="Actualizar">
                      <i class="far fa-edit"></i>
                  </a>
                   <a role="button" class="btn btn-danger" onclick="modal('.$value['id'].')" data-bs-toggle="modal" data-bs-target="#exampleModal"">
                      <i class="far fa-trash-alt"></i>
                  </a>
              </div>
          </div>
';


           $videos[$key] = array(
               $acciones,
               $value['id'],
               $value['title'],
               $value['description'],
               $value['image'],
               $value['video_path'],
               $value['name'],
               $value['email']
           );
       }




       return $videos;
   }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('video.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validación de campos requeridos
$this->validate($request, [
    'title' => 'required|min:5',
    'description' => 'required',
    'video' => 'required|mimes:mp4',
    'image' => 'required|mimes:jpg,jpeg,png,gif'
]);


$video = new Video();
$user = Auth::user();
$video->user_id = $user->id;
$video->title = $request->input('title');
$video->description = $request->input('description');


//Subida de la miniatura
$image = $request->file('image');
if ($image) {
    $image_path = time() . $image->getClientOriginalName();
    Storage::disk('images')->put($image_path, File::get($image));


    $video->image = $image_path;
}


//Subida del video
$video_file = $request->file('video');
if ($video_file) {
    $video_path = time() . $video_file->getClientOriginalName();
    Storage::disk('videos')->put($video_path, File::get($video_file));
    $video->video_path = $video_path;
}


$video->status = 1;


$video->save();
return redirect()->route('videos.index')->with(array(
    'message' => 'El video se ha subido correctamente'
));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $video = Video::findOrFail($id);
        return view('video.edit', array(
            'video' => $video
        ));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'title' => 'required|min:5',
            'description' => 'required',
        ]);


        $user = Auth::user();
        $video = Video::findOrFail($id);
        $video->user_id = $user->id;
        $video->title = $request->input('title');
        $video->description = $request->input('description');


        //Subida de la miniatura
        $image = $request->file('image');
        if ($image) {
            $image_path = time() . $image->getClientOriginalName();
            Storage::disk('images')->put($image_path, File::get($image));


            $video->image = $image_path;
        }


        //Subida del video
        $video_file = $request->file('video');
        if ($video_file) {
            $video_path = time() . $video_file->getClientOriginalName();
            Storage::disk('videos')->put($video_path, File::get($video_file));
            $video->video_path = $video_path;
        }


        $video->status = 1;


        $video->save();
        return redirect()->route('videos.index')->with(array(
            'message' => 'El video se ha actualizado correctamente'
        ));
    }



    /**
     * Remove the specified resource from storage.
     */
    //public function destroy(string $id)
    //{
        //
    //}

    public function delete_video($video_id)
    {
        $video = Video::find($video_id);
        if ($video) {
            $video->status = 0;
            $video->update();
            return redirect()->route('videos.index')->with(array(
                "message" => "El video se ha eliminado correctamente"
            ));
        } else {
            return redirect()->route('videos.index')->with(array(
                "message" => "El video que trata de eliminar no existe"
            ));
        }
    }


}
