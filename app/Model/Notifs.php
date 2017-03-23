<?php
namespace App\Model;

use Illuminate\Http\Request;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
*
*/
class Notifs extends Model
{
    protected $table = 'notifs';
    protected $champs = array('id_notifs', 'created_at', 'updated_at', 'profils_send', 'profils_receive', 'notif', 'lu', 'type');

    public function profils($option = false)
    {
        return $this->belongsTo('App\Model\Profils', 'id_profils', $option);
    }

    public function addNotifs($send, $receive, $type)
    {
        $check = $this->where([
                              ['profils_send', '=', $send->id_profils],
                              ['profils_receive', '=', $receive->id_profils],
                              ['lu', '=', 0],
                              ['type', '=', $type]
                              ])
                        ->get();
        if (count($check) == 0) {
            $this->profils_send = $send->id_profils;
            $this->profils_receive = $receive->id_profils;
            $this->notif = $this->$type($send);
            $this->lu = 0;
            $this->type = $type;
            $this->save();
        } else {
            $check[0]->update();
        }
    }

    public function getNotifNonLu($id)
    {
        $get = $this->where([
                              ['profils_receive', '=', $id],
                              ['lu', '=', 0]
                              ])
                        ->get();
        return $get;
    }

    public function visite($send)
    {
        $notif = $send->pseudo . ' viens de visiter votre profils';
        return $notif;
    }

    public function like($send)
    {
        $notif = $send->pseudo . ' viens de vous liker';
        return $notif;
    }


}
?>
