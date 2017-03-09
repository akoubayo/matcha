<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Profils;
use Illuminate\Http\Response;
use App\Model\Likes;
use App\Model\Shows;

class ProfilController extends Controller
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
  //      $array = array("error" => "An error as occured");
        $me = $request->user();
        $profils = new Profils();
        $profil = $profils->where([['users_id', '=', $me->id]])->get();
        if (count($profil) === 1) {
            $array = $profils->getAllProfil($request, $profil[0]);
        }


        // $profils = new Profils();
        // $profil = $profils->all();
        // $profils->getProfil($profil, null);
        // $array['profils'] = $profil;
        // $array['count'] = $profils->count();
        return Response()->json($array, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    public function me(Request $request, $response = true)
    {
        $me = $request->user();
        $profil = new Profils();
        $profil = $profil->where([['users_id', '=', $me->id]])->get();
        if (count($profil) == 1) {
            $profil[0]->getProfil($profil, null);
            if ($response === true) {
                return response()->json($profil, 200);
            }
            return $profil;
        }
        if ($response === true) {
            return Response()->json(["error" => "An error as occured"]);
        }
        return array();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $req)
    {
        if ($id == "me") {
            return $this->me($req);
        } else {
            $profil = new Profils();
            $profil = $profil->where([['id_profils', '=', $id]])->get();
            if (count($profil) == 1) {
                $me = $req->user();
                $me = $profil[0]->where([['users_id', '=', $me->id]])->get();
                $profil[0]->getProfil($profil, $me[0]);
            }
            return response()->json($profil, 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $me = $request->user();
        $profils = new Profils($me->id);
        $ret = $profils->updateProfil($request);
        return Response()->json($ret, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
