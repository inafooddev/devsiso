<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCluster extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(MasterClusterItem::class, 'master_cluster_id', 'id');
    }
}
