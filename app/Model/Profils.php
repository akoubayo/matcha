<?php
namespace App\Model;

use Illuminate\Http\Request;
use App\Lib\RequestController;
/**
*
*/
class Profils extends Model
{
    protected $table = 'profils';
    protected $champs = array('id_profils','created_at','updated_at','description', 'sexe', 'orientation', 'cheveux', 'yeux', 'poid', 'taille', 'users_id', 'pseudo', 'mail', 'nom', 'prenom', 'birthday','lat','lon', 'ville');

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id) {
            $new = new Profils();
            $new = $new->where([['users_id', '=', $id]])->get();
            if ($new) {
                $this->createVar($new[0]);
            }
        }
    }

    public function photos($option = false)
    {
        return $this->hasMany('App\Model\Photos', 'profils_id', $option);
    }

    public function createVar(Profils $profils)
    {
        foreach ($this->champs as $value) {
            if (isset($profils->$value)) {
                $this->$value = $profils->$value;
            }
        }
    }

    public function photoUser()
    {
        $pho = $this->photos(true)
                        ->where([['sup', '=', '0']])
                        ->get();
        return $pho;
    }

    public function getLocation()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        }
        $query = @unserialize(file_get_contents('http://ip-api.com/php/'.$ip));
        if ($query && $query['status'] == 'success') {
            return $query;
            echo 'Hello visitor from '.$query['country'].', '.$query['city'].'!';
        }
        return array('lat' => 0, 'lon' => 0, 'city' => '');
    }
    public function createProfil($user, Request $req)
    {
        $this->users_id = $user->id;
        $this->mail = $user->email;
        $this->nom = $user->name;
        $this->prenom = $user->first_name;
        $this->pseudo = $user->pseudo;
        $this->sexe = (int) RequestController::control($req->sexe);
        if (!$req->lat || !$req->lon) {
            $coord = $this->getLocation();
            $this->lat = $coord['lat'];
            $this->lon = $coord['lon'];
            if(!$req->ville) {
                $this->ville = RequestController::control($coord['city']);
            }
        } else {
            $this->lat = RequestController::control($req->lat);
            $this->lon = RequestController::control($req->lon);
            $this->ville  = RequestController::control($req->ville);
        }
        $save = $this->save();
        return $save;
    }


    public function updateProfil(Request $request)
    {
        $array = array('description', 'orientation', 'cheveux', 'yeux', 'poid', 'taille', 'birthday');
        foreach ($this->champs as $value) {
            if (in_array($value, $array) && !empty($request->input($value))) {
                $this->$value = RequestController::control($request->input($value));
            }
        }
        $this->update();
        return $this;
    }

    public function getProfil(Request $request)
    {

    }
}
