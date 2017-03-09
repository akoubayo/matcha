<?php
namespace App\Model;

use App\Model\Model;
use Illuminate\Http\Request;
use App\Lib\RequestController;

/**
*
*/
class TagTaggable extends Model
{
    protected $table = 'tags_taggable';
    protected $champs = array('id_tags_taggable','created_at','updated_at','tags_id','profils_id');

    public function tags($option = false)
    {
        return $this->belongsTo('App\Model\Tags', 'id_tags', $option);
    }

    public function addTaggable($profil, $tag)
    {
        $exist = $this->where([['profils_id', '=', $profil], ['tags_id', '=', $tag]])->get();
        if (count($exist) === 0) {
            $this->profils_id = $profil;
            $this->tags_id = $tag;
            $this->save();
        }
        return array("success" => "tags save");
    }
}
