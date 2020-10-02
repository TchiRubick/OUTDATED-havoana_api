<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    const CREATED_AT = 'prd_datecrea';
    const UPDATED_AT = 'prd_datemodif';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 't_produit';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'prd_idinterne';

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
