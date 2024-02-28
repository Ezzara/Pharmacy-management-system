<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'category_id','transaction_id','quantity','total_price','total_price'
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function purchase(){
        return $this->belongsTo(Purchase::class);
    }

}
