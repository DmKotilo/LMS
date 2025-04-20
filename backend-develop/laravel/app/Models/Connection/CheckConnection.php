<?php

namespace App\Models\Connection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckConnection extends Model
{
    use HasFactory;

    protected $table = "check_connections";

    protected $fillable = [
        "date_current"
    ];
}
