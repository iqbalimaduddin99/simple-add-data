<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id',
        'transaction_category_id',
        'name',
        'value_idr',
    ];

    public function header()
    {
        return $this->belongsTo(TransactionHeader::class, 'transaction_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'transaction_category_id');
    }
}
