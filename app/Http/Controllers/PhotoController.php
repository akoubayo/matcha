<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use League\Flysystem\Filesystem;
use App\Model\Photos;
use App\Model\Profils;
//use League\Flysystem\Adapter\Local;


class PhotoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = ($request->limit) ? (int)$request->limit : 100;
        $offset = ($request->offset === 0) ? (int)$request->offset : 0;
        $order = ($request->order === "desc" || $request->order == "asc") ? $request->order : "desc";
        $photo = new Photos();
        $photos = $photo->select()
                        ->where([['sup', '=', 0]])
                        ->order([['id_photos', $order]])
                        ->limit($limit)
                        ->offset($offset)
                        ->get();
        return Response()->json($photos, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $me = $request->user();
        $profils = new profils($me->id);
        $newPhoto = new Photos($profils->id_profils);
        $count = $newPhoto->countImg();
        if ($count < 50) {
            $profil = ((int)$count === 0) ? true : false;
            $new = $newPhoto->saveImg($request, $profil);
            return Response()->json($new, 200);
        } else {
            return Response()->json(array('error' => 'too much pictures'), 200);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->profil) {
            $me = $request->user();
            $profils = new profils($me->id);
            $photo = new Photos($profils->id_profils);
            return Response()->json($photo->photoProfil($request->profil, $id));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $me = $request->user();
        $profils = new profils($me->id);
        $photo = new Photos($profils->id_profils);
        $del = $photo->where([['id_photos', '=', (int)$id], ['profils_id', '=', $profils->id_profils]])->get();
        if (count($del) === 1) {
            $del[0]->sup = true;
            $del[0]->profil = 0;
            $del = $del[0]->update();
            return Response()->json(["succes" => "Img delete"], 200);
        }
        return Response()->json(["error" => "An error as occure"], 200);
    }
}
