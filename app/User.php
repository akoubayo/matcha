<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\User;
use App\Model\Profils;
use App\Lib\RequestController;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'first_name', 'pseudo', 'email'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function unsetVar($obj)
    {
        foreach ($obj as $key => $value) {
            if (!in_array($key, $this->fillable)) {
                unset($obj->$key);
            }
        }
    }

    public function testNewUser(Request $req)
    {
        $sexe = (int)RequestController::control($req->sexe);
        if ($sexe != 1 && $sexe != 2) {
            return ["sexe" => "invalid"];
        }
        $test = DB::table('users')
                    ->where('pseudo',RequestController::control($req->pseudo))
                    ->orWhere('email', RequestController::control($req->email))
                    ->get();
        $error = array();
        foreach ($test as $value) {
            if (strtoupper($value->pseudo) == strtoupper(RequestController::control($req->pseudo))) {
                $error['pseudo'] = "exist";
            }
            if (strtoupper($value->email) == strtoupper(RequestController::control($req->email))) {
                $error['email'] = "exist";
            }
        }
        return $error;
    }

    public function createPerso(Request $req)
    {
        $new = $this->testNewUser($req);
        if (count($new) == 0) {
            $new = new User();
            $new->name = RequestController::control($req->name);
            $new->email = RequestController::control($req->email);
            $new->password = bcrypt($req->password);
            $new->pseudo = RequestController::control($req->pseudo);
            $new->first_name = RequestController::control($req->input('first_name'));
            $new->save();
            $profil = new Profils($new);
            $new = $profil->createProfil($new,$req);
        }
        return $new;
    }
}
