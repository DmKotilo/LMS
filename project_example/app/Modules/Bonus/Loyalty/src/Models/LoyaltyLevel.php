<?php

namespace Loyalty\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LoyaltyLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_days',
        'discount_percent',
        'coefficient',
    ];



}
