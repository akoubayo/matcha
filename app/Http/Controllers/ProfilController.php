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
        $array = array();
        $profils = new Profils();
        $profil = $profils->all();
        foreach ($profil as $key => $value) {
            $value->photo = $value->photoUser();
            $value->likes = count($value->likes());
        }
        $array['profils'] = $profil;
        $array['count'] = $profils->count();
        return response()->json($array, 200);
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

    public function me(Request $request)
    {
        $me = $request->user();
        $profil = new Profils();
        $profil = $profil->where([['users_id', '=', $me->id]])->get();
        if (count($profil) == 1) {
                $profil[0]->photo = $profil[0]->photoUser();
                $profil[0]->nbPhoto = count($profil[0]->photo);
                $profil[0]->likes = count($profil[0]->likes());
        }
        return response()->json($profil, 200);
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
                $profil[0]->photo = $profil[0]->photoUser();
                $addLike = new Likes();
                $showlike = $profil[0]->likes();
                $profil[0]->nbLikes = count($showlike);
                $profil[0]->likes = $showlike;
                $profil[0]->visitorLikes = $addLike->visitorLikes($showlike);
                $addshow = new shows();
                $addshow->saveShows($profil[0]->id_profils, $me->id);
                $showlike = $profil[0]->shows();
                $profil[0]->nbshows = count($showlike);
                $profil[0]->shows = $showlike;
                $profil[0]->visitorshows = $addshow->visitorShows($showlike);
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
