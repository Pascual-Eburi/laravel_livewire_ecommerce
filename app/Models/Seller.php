<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Seller extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "address",
        "payment_method",
        "payment_email"
    ];


    /**
     * Get the user associated whit the seller.
     */

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
