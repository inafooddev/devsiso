<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicApi extends Model
{
    protected $fillable = [
        'endpoint',
        'method',
        'sql_query',
        'description',
    ];
}
