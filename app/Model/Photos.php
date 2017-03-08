<?php
namespace App\Model;

use Illuminate\Http\Request;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
*
*/
class Photos extends Model
{

    protected $table = 'photos';
    protected $champs = array('id_photos', 'created_at', 'updated_at', 'name', 'type', 'small', 'valid', 'sup', 'profil', 'vote', 'profils_id');

    public function __construct($id = null)
    {
        parent::__construct();
        if ($id) {
           $this->profils_id = $id;
        }
    }

    public function profils($option = false)
    {
        return $this->belongsTo('App\Model\Profils', 'id_profils', $option);
    }

    public function commentaires($option = false)
    {
        return $this->hasMany('App\Model\Commentaires', 'photos_id', $option);
    }

    public function countImg()
    {
        return $this->count(true)->where([['profils_id', '=', $this->profils_id]])->get();
    }

    public function createImg($src)
    {
        $mime = getimagesize($src);
        switch ($mime['mime']) {
            case 'image/png':
                $source = imagecreatefrompng($src);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($src);
                break;
            case 'image/jpeg':
                $source = imagecreatefromjpeg($src);
                break;
            case 'image/jpg':
                $source = imagecreatefromjpeg($src);
                break;
            default:
                $source = null;
                break;
        }
        return $source;
    }

    public function getMine($img)
    {
        $mime = getimagesize($img);
        return $mime['mime'];
    }

    public function validMine($ext)
    {
        $valid = array('png', 'gif', 'jpeg', 'jpg');
        return in_array($ext, $valid);
    }

    public function redimensionner($src, $lar, $hau)
    {
        $taille = getimagesize($src);
        $newImg = imagecreatetruecolor($lar, $hau);
        $coef = min($taille[0]/$lar, $taille[1]/$hau);
        $deltax = $taille[0]-($coef * $lar);
        $deltay = $taille[1]-($coef * $hau);
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);
        if ($imgTmp = $this->createImg($src)) {
            imagecopyresampled($newImg, $imgTmp, 0, 0, $deltax/2, $deltay/2, $lar, $hau, $taille[0]-$deltax, $taille[1]-$deltay);
            return $newImg;
        }
        return null;
    }

    public function saveImg(Request $request, $profil)
    {
        if ($request->file('photo')->isValid() && $this->validMine($request->photo->extension())) {
            $file = $request->photo->store('images');
            $path = storage_path() . '/app/'.$file;
            if ($small =  $this->redimensionner($path, 200, 200)) {
                $name = md5($file).'_small.png';
                $path_small = storage_path() . '/app/small/'.$name;
                imagepng($small, $path_small);
                return $this->savePicture($file, 'small/'.$name, substr($this->getMine($path), 6), $profil);
            }
        }
        return array("error" => "Error avec le fichier envoyé");
    }

    public function savePicture($ori, $small, $type, $profil)
    {
        $this->name = $ori;
        $this->small = $small;
        $this->type = $type;
        $this->valid = true;
        $this->sup = false;
        $this->profil = $profil;
        $this->vote = 0;
        $save = $this->save();
        return $save;
    }

    public function photoProfil($profil, $id)
    {
        $photoProfil = $this->where([
                                        ['profils_id', '=', $this->profils_id],
                                        ['id_photos', '=', (int)$id]
                                    ])
                            ->get();
        if (count($photoProfil) === 0) {
            return array('error' => 'An error as occure');
        }
        if ((int)$profil === 1) {
            $count = $this  ->where([
                                        ['profils_id', '=', $this->profils_id],
                                        ['profil', '=', 1]
                                    ])
                            ->get();
            if (count($count) > 0) {
                foreach ($count as $key => $value) {
                    $value->profil = 0;
                    $value->update();
                }
            }
            $photoProfil[0]->profil = 1;
            $photoProfil[0]->update();
            return $photoProfil[0];
        }
    }

    public function delete($src)
    {
        return;
    }
}
