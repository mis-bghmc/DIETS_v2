<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hdocord extends Model
{
    use HasFactory;

    protected $table = 'hdocord';
    public $timestamps = false;
    protected $fillable = [
        'docointkey',
        'enccode',
        'hpercode',
        'dodate',
        'licno',
        'locked',
        'dietcode',
        'dostatus',
        'entryby',
    ];

}
