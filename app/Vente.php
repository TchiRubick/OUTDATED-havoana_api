<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    const CREATED_AT = 'vnt_date';
    const UPDATED_AT = 'vnt_update';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 't_vente';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'vnt_id';

    protected $fillable = [
        'vnt_mag',
        'vnt_caisse',
        'vnt_userid',
        'vnt_date',
        'vnt_prix',
        'vnt_type',
        'vnt_quantite',
        'vnt_prdid',
        'vnt_update'
    ];

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
