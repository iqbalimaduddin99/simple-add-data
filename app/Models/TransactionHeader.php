<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionHeader extends Model
{
    protected $fillable = [
        'desc',
        'code',
        'rate_euro',
        'date_paid',
    ];
    
    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }
}
