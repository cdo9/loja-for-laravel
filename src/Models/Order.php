<?php

namespace Suavy\LojaForLaravel\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Stripe\PaymentIntent;

class Order extends Model
{
    use CrudTrait;

    protected $table = 'loja_orders';
    // Disable Laravel's mass assignment protection
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity', 'price', 'price_with_tax');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Functions
    |--------------------------------------------------------------------------
    */
    public static function initOrder(PaymentIntent $paymentIntent)
    {
        $order = self::create([
            'user_id' => Auth::id(),
            'order_status_id' => 1, // pending
            'stripe_payment_intent_id' => $paymentIntent->id,
        ]);
        // todo complete loja_order_... tables
        return $order;
    }

    public static function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent)
    {
        // update order_status_id to "processed"
        self::query()->where('stripe_payment_intent_id', $paymentIntent->id)->update(['order_status_id' => 2]);
        // todo send success email
    }
}
