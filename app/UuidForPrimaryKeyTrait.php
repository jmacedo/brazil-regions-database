<?php

namespace App;

use Illuminate\Support\Str;

trait UuidForPrimaryKeyTrait
{
    public static function boot()
    {
        parent::boot();

        self::creating(function($model) {
            $model->id = Str::uuid();
        });
    }
}
