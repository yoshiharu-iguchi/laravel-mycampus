<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = ['name','address','nearest_station'];

    public function transportRequests()
    {
        return $this->hasMany(TransportRequest::class);
    }
}
