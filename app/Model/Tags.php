<?php
namespace App\Model;

use App\Model\Model;
use Illuminate\Http\Request;
use App\Lib\RequestController;
use App\Model\TagTaggable;

/**
*
*/

class Tags extends Model
{
    protected $table = 'tags';
    protected $champs = array('id_tags','created_at','updated_at','name');

    public function addTags(Profils $profil, Request $request)
    {
        $name = RequestController::control($request->nameTags);
        if (!empty($name)) {
            $exist = $this->where([['name', '=', ucfirst(mb_strtolower($name))]])->get();
            $add = new TagTaggable();
            if (count($exist) === 0) {
                $this->name = ucfirst(mb_strtolower($name));
                $newTag = $this->save();
                return $add->addTaggable($profil->id_profils, $newTag->id_tags, $newTag->name);
            } else {
                return $add->addTaggable($profil->id_profils, $exist[0]->id_tags, $exist[0]->name);
            }
        }
        return array("error" => "An error as occured");
    }

    public function deleteTags(Profils $profil, $id)
    {
        $exist = $this->where([['id_tags', '=', (int)$id]])->get();
        if (count($exist) !== 0) {
            echo 'toto';
            $add = new TagTaggable();
            return $add->deleteTaggable($profil->id_profils,(int)$id);
        }
        return array("error" => "An error as occured");
    }
}
