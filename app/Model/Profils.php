<?php
namespace App\Model;

use Illuminate\Http\Request;
use App\Lib\RequestController;
use App\Model\Likes;
use App\Model\Shows;
use App\Model\Notifs;

/**
*
*/
class Profils extends Model
{
    protected $table = 'profils';
    protected $champs = array('id_profils','created_at','updated_at','description', 'sexe', 'orientation', 'cheveux', 'yeux', 'poid', 'taille', 'users_id', 'pseudo', 'prenom', 'birthday','lat','lon', 'ville');
    protected $order;
    protected $limit;
    protected $offset;
    protected $asc;

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

    public function likes($option = false)
    {
        return $this->hasMany('App\Model\Likes', 'profils_id', $option);
    }

    public function shows($option = false)
    {
        return $this->hasMany('App\Model\Shows', 'profils_id', $option);
    }

    public function tags($option = false)
    {
        $taggables = $this->hasMany('App\Model\TagTaggable', 'profils_id', $option, 'tags_taggable');
        $tags = array();
        foreach ($taggables as $value) {
            $tag = $value->tags();
            $tags[] = array("id" => $tag->id_tags,"name" => $tag->name);
        }
        return $tags;
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
            //echo 'Hello visitor from '.$query['country'].', '.$query['city'].'!';
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
            if (!$req->ville) {
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
        $array = array('description', 'orientation', 'cheveux', 'yeux', 'poid', 'taille', 'birthday', 'ville');
        foreach ($this->champs as $value) {
            if (in_array($value, $array) && !empty($request->input($value))) {
                if ($value == 'yeux') {
                    $this->$value = $this->updateYeux($request->input($value));
                } else if($value == 'cheveux') {
                    $this->$value = $this->updateCheveux($request->input($value));
                } else if($value == 'orientation') {
                    $this->$value = $this->updateOrientation($request->input($value));
                } else {
                    $this->$value = RequestController::control($request->input($value));
                }
            }
        }
        $this->update();
        return $this;
    }

    public function search(Request $request)
    {
        $this->limit = ($request->limit) ? (int)$request->limit : 5;
        $this->offset = ($request->offset) ? (int)$request->offset : 0;
        if ($request->order) {
            $order = explode(';', $request->order);
            foreach ($order as $value) {
                $ex = explode(':', $value);
                if (is_array($ex) && count($ex) === 2 && in_array($ex[0], $this->champs)) {
                    $ex[1] = ($ex[1] === 'asc' || $ex[1] == 'desc') ? $ex[1] : 'desc';
                    $this->order[] = array($ex[0], $ex[1]);
                }
            }
        } else {
            $this->order = [['id_profils', 'desc']];
        }
    }

    public function getSearch(Request $request, $me)
    {
        $where = array(['id_profils', '!=', $me->id_profils]);
        $sign = array('=', '<' ,'>', '<=', '>=', '!=');
        if ($request->where) {
            $order = explode(';', $request->where);
            foreach ($order as $value) {
                $ex = explode(':', $value);
                if (is_array($ex) && count($ex) === 3 && in_array($ex[0], $this->champs)) {
                    $ex[1] = (in_array($ex[1], $sign)) ? $ex[1] : '=';
                    $ex[2] = RequestController::control($ex[2]);
                    $where[] = array($ex[0], $ex[1], $ex[2]);
                }
            }
        }
        if ($me->orientation == 1) {
            $where[] = array("sexe", "!=", $me->sexe);
            $where[] = array("orientation", "!=", 2);
        }
        if ($me->orientation == 2) {
            $where[] = array("sexe", "=", $me->sexe);
            $where[] = array("orientation", "!=", 1);
        }
        if ($me->orientation == 3) {
            $where[] = array('((','sexe', '=', $me->sexe, 'AND ' );
            $where[] = array('', 'orientation', '!=', 1 , ') OR ');
            $where[] = array('(', 'sexe', '!=', $me->sexe, '))');
        }
        return $where;
    }

    public function getAllProfil(Request $request, $me)
    {
        $this->search($request);
        $where = $this->getSearch($request, $me);
        $profils = $this->where($where)
                        ->order($this->order)
                        ->limit($this->limit)
                        ->offset($this->offset)
                        ->get();
        $this->getProfil($profils);
        $ret['profils'] = $profils;
        $ret['nbProfil'] = $this->count(true)
                                ->where($where)
                                ->get();
        $ret['order'] = $this->order;
        $ret['limit'] = $this->limit;
        $ret['offset'] = $this->offset;
        return $ret;
    }

    public function getProfil($profils, $me = null, $all = false)
    {
        foreach ($profils as $profil) {
            if (isset($profil->id_profils)) {
                if ($all === false) {
                    $profil->photo = $profil->photoUser();
                    $profil->nbPhoto = count($profil->photo);
                    $profil->getLikes($profil);
                    $profil->getShow($profil, $me);
                    $profil->getTags($profil);
                }
                $this->getCheveux($profil);
                $this->getSexe($profil);
                $this->getYeux($profil);
                $this->orientation($profil);
                //$this->description =  nl2br($this->description);
            }
        }
    }

    public function getLikes($profil)
    {
        $addLike = new Likes();
        $showlike = $profil->likes();
        //$profil->likes = $showlike;
        $profil->visitorLikes = $addLike->visitorLikes($showlike);
        $profil->nbLikes = count($showlike);
    }

    public function getShow($profil, $me)
    {
        $addshow = new shows();
        if ($me != null && $me->id_profils != $profil->id_profils) {
            $addshow->saveShows($profil->id_profils, $me->id_profils);
            $newNotif = new notifs();
            $newNotif->addNotifs($me,$profil,'visite');
        }
        $showlike = $profil->shows();
        $profil->nbshows = count($showlike);
        //$profil->shows = $showlike;
        $profil->visitorshows = $addshow->visitorShows($showlike);
    }

    public function getTags($profil)
    {
        $profil->tags = $this->tags();
        $profil->nbTags = count($profil->tags);
    }

    public function getSexe($profil)
    {
        switch ($profil->sexe) {
            case '1':
                $profil->sexe = "Homme";
                break;
            case '2':
                $profil->sexe = "Femme";
                break;
            default:
                $profil->sexe = "Inconnue";
                break;
        }
    }

    public function getCheveux($profil)
    {
        switch ($profil->cheveux) {
            case '1':
                $profil->cheveux = 'Brun';
                break;
            case '2':
                $profil->cheveux = 'Blond';
                break;
            case '3':
                $profil->cheveux = 'Roux';
                break;
            case '4':
                $profil->cheveux = 'Chatain';
                break;
            case '5':
                $profil->cheveux = 'Noir';
                break;
            default:
                $profil->cheveux = 'Inconnue';
                break;
        }
    }

    public function updateCheveux($profil)
    {
        switch (mb_strtolower($profil)) {
            case 'brun':
                $profil = 1;
                break;
            case 'blond':
                $profil = 2;
                break;
            case 'roux':
                $profil = 3;
                break;
            case 'chatain':
                $profil = 4;
                break;
            case 'noir':
                $profil = 5;
                break;
            default:
                $profil = 0;
                break;
        }
        return $profil;
    }

    public function getYeux($profil)
    {
        switch ($profil->yeux) {
            case '1':
                $profil->yeux = 'Bleu';
                break;
            case '2':
                $profil->yeux = 'Marron';
                break;
            case '3':
                $profil->yeux = 'Vert';
                break;
            case '4':
                $profil->yeux = 'Gris';
                break;
            default:
                $profil->yeux = 'Inconnue';
                break;
        }
    }

    public function updateYeux($profil)
    {
        switch (mb_strtolower($profil)) {
            case 'bleu':
                $profil = 1;
                break;
            case 'marron':
                $profil = 2;
                break;
            case 'vert':
                $profil = 3;
                break;
            case 'gris':
                $profil = 4;
                break;
            default:
                $profil = 0;
                break;
        }
        return $profil;
    }

    public function orientation($profil)
    {
        switch ($profil->orientation) {
            case '1':
                $profil->orientation = 'Hétérosexuel';
                break;
            case '2':
                $profil->orientation = 'Homosexuel';
                break;
            case '3':
                $profil->orientation = 'Bisexuel';
                break;
            default:
                $profil->orientation = 'Bisexuel';
                break;
        }
    }

    public function updateOrientation($profil)
    {
        switch (mb_strtolower($profil)) {
            case 'hétérosexuel':
                $profil = 1;
                break;
            case 'homosexuel':
                $profil = 2;
                break;
            case 'bisexuel':
                $profil = 3;
                break;
            default:
                $profil = 4;
                break;
        }
        return $profil;
    }
}
