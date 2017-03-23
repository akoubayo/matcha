<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Profils;
use App\Model\Tags;

class TagsController extends Controller
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
    public function index()
    {
        //
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
        $ret = array("error" => "An error as occured");
        $me = $request->user();
        $profil = new Profils();
        $profil = $profil->where([['users_id', '=', $me->id]])->get();

        if (count($profil) === 1 && $request->nameTags) {
            $tags = new Tags();
            $ret = $tags->addTags($profil[0], $request);
        }
        return Response()->json($ret, 200);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $ret = array("error" => "An error as occured");
        $me = $request->user();
        $profil = new Profils();
        $profil = $profil->where([['users_id', '=', $me->id]])->get();

        if (count($profil) === 1 && $id) {
            $tags = new Tags();
            $ret = $tags->deleteTags($profil[0], $id);
        }
        return Response()->json($ret, 200);
    }
}
