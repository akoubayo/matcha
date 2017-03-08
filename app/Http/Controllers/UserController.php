<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Http\Response;
use App\Model\Profils;

class UserController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth:api')->except('store', 'test');
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
        $array['profils'] = $profil;
        $array['count'] = $profils->count();
        return response()->json($array, 200);
    }

    public function me(Request $request)
    {
        $me = $request->user();
        $profil = new Profils();
        $profil = $profil->where([['users_id', '=', $me->id]])->get();
        return response()->json($profil, 200);
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->has(['name', 'password', 'email', 'pseudo', 'first_name'])) {
            $user = new User();
            $ret = $user->createPerso($request);
            return response()->json($ret, 200);
        } else {
            return response()->json(['error','Bad Request'], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $req)
    {
        if ($id == "me") {
            return $this->me($req);
        } else {
            $profil = new Profils();
            $profil = $profil->where([['users_id', '=', $id]])->get();
            return response()->json($profil, 200);
        }
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
    public function destroy($id)
    {
        //
    }
}
