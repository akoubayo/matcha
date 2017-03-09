<?php
namespace App\Model;

use Illuminate\Http\Request;

/**
*
*/
class Shows extends Model
{
    protected $table = 'shows';
    protected $champs = array('id_shows', 'created_at', 'updated_at', 'profils_id', 'visitor');

    public function saveShows($id, $visitor)
    {
        $check = $this->where([
                                  [
                                      'profils_id', '=', $id
                                  ],
                                  [
                                      'visitor', '=', $visitor
                                  ]
                              ])
                        ->get();
        if (count($check) === 0) {
            $this->profils_id = $id;
            $this->visitor = $visitor;
            $this->save();
        } else {
            $check[0]->update();
        }
    }

    public function visitorShows($shows)
    {
        if (count($shows) == 0) {
            return array();
        }
        $array = array();
        foreach ($shows as $value) {
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
