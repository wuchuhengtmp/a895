<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address';

    protected $fillable = [
        'name',
        'user_id',
        'address',
        'phone',
        'is_default',
    ];

    public function city()
    {
        return $this->hasOne(ChinaArea::class, 'code', 'city_code');
    }

    public function getPhoneAttribute($phone)
    {
        $phone = substr_replace($phone,'****',3,4);
        return $phone;
    }
}
