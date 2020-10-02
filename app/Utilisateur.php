<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    const CREATED_AT = 'utl_datecrea';
    const UPDATED_AT = 'utl_datemodif';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_utilisateur';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'utl_idinterne';

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
