<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transac extends Model
{
    const CREATED_AT = 'tsc_datecrea';
    const UPDATED_AT = 'tsc_datemodif';

    protected $table = 't_transac';

    protected $fillable   = [
        'tsc_idexterne',
        "tsc_action",
        "tsc_statut",
        "tsc_montant",
        "tsc_origin",
        "tsc_user",
        "tsc_device"
    ];

    protected $primaryKey = 'tsc_idinterne';

    public $incrementing = true;

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
