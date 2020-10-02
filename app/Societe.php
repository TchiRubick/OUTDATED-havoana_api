<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Societe extends Model
{
    const CREATED_AT = 'soc_datecrea';
    const UPDATED_AT = 'soc_datemodif';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 't_societe';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'soc_idinterne';

    protected $connection = 'default';
}
