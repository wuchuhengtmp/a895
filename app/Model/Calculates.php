<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Calculates extends Model
{
    protected $table =  'calculates';

    protected $fillable = [
        'key',
        'value',
        'type'
    ];

    public function city()
    {
        return $this->hasOne(ChinaArea::class, 'code', 'key');
    }

    /**
    * 房间均价
    *
    */
    public function getPriceBykey(string $key) : float
    {
        $Row = self::where('key', $key)
            ->select(['value'])
            ->first();
        return round($Row->value, 2);
    }   
}
