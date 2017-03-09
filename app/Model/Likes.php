<?php
namespace App\Model;

use Illuminate\Http\Request;
use App\Model\Photos;

/**
*
*/
class Likes extends Model
{
    protected $table = 'likes';
    protected $champs = array('id_likes', 'created_at', 'updated_at', 'profils_id', 'visitor');

    public function saveLikes($id, $visitor)
    {
        if ((int)$id === 0 || (int)$visitor === 0 || (int)$id === (int)$visitor) {
            return array('error' => 'An error as occured');
        }
        $checkPhoto = new Photos();
        $checkPhoto = $checkPhoto->where([['profils_id', '=', $visitor], ['sup', '=', 0]])
                                ->limit(1)
                                ->offset(0)
                                ->get();
        if (count($checkPhoto) == 0) {
            return array('error' => 'You must have one picture on your profil');
        }
        $check = $this->where([
                                  [
                                      'profils_id', '=', $id
                                  ],
                                  [
                                      'visitor', '=', $visitor
                                  ]
                              ])
                        ->get();
        $this->foreignClass = 'App\Model\Profils';
        $verif = $this->select('profils')->where([['id_profils', '=', $id]])->get();
        if (count($verif) === 0) {
            return array('error' => 'Users not exist');
        }
        if (count($check) === 0) {
            $this->profils_id = $id;
            $this->visitor = $visitor;
            $this->save();
        } else {
            $check[0]->update();
        }
        return array('succes' => 'Likes is save');
    }

    public function visitorLikes($likes)
    {
        if (count($likes) == 0) {
            return array();
        }
        $array = array();
        foreach ($likes as $value) {
            $array[] = ['id_profils', '=', $value->visitor];
        }
        $this->foreignClass = 'App\Model\Profils';
        $ret = $this->select("profils")->whereOr($array)->get();
        if (count($ret) > 0) {
            $ret[0]->getProfil($ret, null, true);
        }
        return $ret;
    }
}
