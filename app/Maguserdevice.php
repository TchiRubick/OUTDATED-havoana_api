<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Maguserdevice extends Model
{

    protected $table = 'tr_maguserdevice';

    protected $fillable = [
        'magudvc_id',
        'magudvc_device',
        'magudvc_user',
        'magudvc_mag'
    ];

    protected $primaryKey = 'magudvc_id';

    public $incrementing = false;

    protected $connection = 'client';

    public static function setInfosDb(Societe $dbInfos)
    {
        config(['database.connections.client.host'      => $dbInfos->soc_hostbase]);
        config(['database.connections.client.port'      => $dbInfos->soc_portbase]);
        config(['database.connections.client.database'  => $dbInfos->soc_namebase]);
        config(['database.connections.client.username'  => $dbInfos->soc_userbase]);
        config(['database.connections.client.password'  => $dbInfos->soc_passbase]);
    }
}
