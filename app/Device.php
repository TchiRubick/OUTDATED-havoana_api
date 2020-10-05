<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    const CREATED_AT = 'dvc_datecrea';
    const UPDATED_AT = 'dvc_datemodif';

    protected $table = 't_devices';

    protected $fillable = [
        'dvc_detail',
        'dvc_idexterne',
        'dvc_statut'
    ];

    protected $primaryKey = 'dvc_idexterne';

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
