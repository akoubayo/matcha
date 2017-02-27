<?php
namespace App\Model;

use Illuminate\Http\Request;

/**
*
*/
class Profils extends Model
{
    protected $table = 'profils';
    protected $champs = array('id_profils','description', 'sexe', 'orientation', 'cheveux', 'yeux', 'poid', 'taille', 'users_id', 'pseudo', 'mail', 'nom', 'prenom', 'birthday');

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id) {
            $new = new Profils();
            $new = $new->where([['users_id', '=', $id]])->get();
            if($new) {
                $this->createVar($new[0]);
            }
        }

    }

    public function createVar(Profils $profils)
    {
        foreach ($this->champs as $value) {
            if (isset($profils->$value)) {
                $this->$value = $profils->$value;
            }
        }
    }

    public function createProfil($user)
    {
        $this->users_id = $user->id;
        $this->mail = $user->email;
        $this->nom = $user->name;
        $this->prenom = $user->first_name;
        $this->pseudo = $user->pseudo;
        $save = $this->save();
        return $save;
    }

    public function updateProfil(Request $request)
    {
        $array = array('description', 'orientation', 'cheveux', 'yeux', 'poid', 'taille', 'birthday');
        foreach ($this->champs as $value) {
            if (in_array($value, $array) && !empty($request->$value)) {
                $this->$value = $request->value;
            }
        }
        $this->description = $request->description;
        $this->update();
        return $this;
    }
}
