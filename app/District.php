<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\UuidForPrimaryKeyTrait;

class District extends Model
{
    use UuidForPrimaryKeyTrait;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
