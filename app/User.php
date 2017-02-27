<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\User;
use App\Model\Profils;

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
        $test = DB::table('users')
                    ->where('pseudo', $req->pseudo)
                    ->orWhere('email', $req->email)
                    ->get();
        $error = array();
        foreach ($test as $value) {
            if ($value->pseudo == $req->pseudo) {
                $error['pseudo'] = "exist";
            }
            if ($value->email == $req->email) {
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
            $new->name = $req->name;
            $new->email = $req->email;
            $new->password = bcrypt($req->password);
            $new->pseudo = $req->pseudo;
            $new->first_name = $req->first_name;
            $new->save();
            $profil = new Profils($new);
            $new = $profil->createProfil($new);
        }
        return $new;
    }
}
