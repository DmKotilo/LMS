<?php

namespace Connect\Models;

use Illuminate\Database\Eloquent\Model;

class CheckConnection extends Model
{
    protected $table = 'check_connections';

    protected $fillable = [
        'date_current',
    ];
}
