<?php

namespace App\Models;

enum DELIVERY_STATUS: string
{
    case PROCESS = 'PROSES';
    case LOADED = 'DIMUAT';
    case IN_DELIVERY = 'SEDANG_DIKIRIM';
    case IN_TRANSIT = 'TRANSIT';
    case COMPLETED = 'SELESAI';

    public function label(): string
    {
        return match($this) {
            self::PROCESS     => 'Proses',
            self::LOADED      => 'Dimuat',
            self::IN_DELIVERY => 'Sedang Dikirim',
            self::IN_TRANSIT  => 'Transit',
            self::COMPLETED   => 'Selesai',
        };
    }
}
