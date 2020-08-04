<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\UuidForPrimaryKeyTrait;

class State extends Model
{
    use UuidForPrimaryKeyTrait;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name', 'initials'
    ];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
