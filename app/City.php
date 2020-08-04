<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\UuidForPrimaryKeyTrait;

class City extends Model
{
    use UuidForPrimaryKeyTrait;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name'
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
