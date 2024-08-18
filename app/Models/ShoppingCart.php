<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShoppingCart extends Model
{
    use HasFactory;

    // モデルにするテーブルを指定
    protected $table = 'shoppingcart';

    public static function getCurrentUserOrders($user_id)
    {
        $shoppingcarts = DB::table('shoppingcart')->where("instance", "{$user_id}")->get();

        $order = [];

        foreach ($shoppingcarts as $order) {
            $orders[] = [
                'id' => $order->number,
                'created_at' => $order->updated_at,
                'total' => $order->price_total,
                'user_name' => User::find($order->instance)->name,
                'code' => $order->code
            ];
        }

        // モデルでのreturnはデータベースから何を取ってくるか
        return $orders;
    }
}
