<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class CheckoutController extends Controller
{
    // 注文内容の確認ページ
    public function index()
    {
        // メモ：Cart::content() = カートの中身を取ってくる
        $cart = Cart::instance(Auth::user()->id)->content();

        $total = 0;
        $has_carriage_cost = false;
        $carriage_cost = 0;

        foreach ($cart as $c) {
            $total += $c->qty * $c->price;
            if ($c->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        if($has_carriage_cost) {
            $total += env('CARRIAGE');
            $carriage_cost = env('CARRIAGE');
        }

        return view('checkout.index', compact('cart', 'total', 'carriage_cost'));
    }


    // Stripe APIに支払い情報を送信し、Stripeの決済ページにリダイレクト
    public function store(Request $request)
    {
        $cart = Cart::instance(Auth::user()->id)->content();

        $has_carriage_cost = false;

        foreach ($cart as $product) {
            if ($product->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        Stripe::setApikey(env('STRIPE_SECRET'));

        $line_items = [];

        foreach ($cart as $product) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $product->price,
                ],
                'quantity' => $product->qty,
            ];
        }

        if ($has_carriage_cost) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => '送料',
                    ],
                    'unit_amount' => env('CARRIAGE'),
                ],
                'quantity' => 1,
            ];
        }

        $checkout_session = Session::create([
            // 支払い対象となる商品
            'line_items' => $line_items,
            // 支払いモード（今回は一回限りの支払い設定）
            'mode' => 'payment',
            // 決済成功時のリダイレクト先URL
            'success_url' => route('checkout.success'),
            // 決済キャンセル時のリダイレクト先URL
            'cancel_url' => route('checkout.index'),
        ]);

        return redirect($checkout_session->url);
    }


    // 決済完了後の案内ページを表示する
    public function success()
    {
        $user_shoppingcarts = DB::table('shoppingcart')->get();
        $number = DB::table('shoppingcart')->where('instance', Auth::user()->id)->count();

        $count = $user_shoppingcarts->count();

        $count += 1;
        $number += 1;
        $cart = Cart::instance(Auth::user()->id)->content();

        $price_total = 0;
        $qty_total = 0;
        $has_carriage_cost = false;

        foreach ($cart as $c) {
            $price_total += $c->qty * $c->price;
            $qty_total += $c->qty;
            if ($c->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        Cart::instance(Auth::user()->id)->store($count);

        DB::table('shoppingcart')->where('instance', Auth::user()->id)
            ->where('number', null)
            ->update([
                'code' => substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 10),
                'number' => $number,
                'price_total' => $price_total,
                'qty' => $qty_total,
                'buy_flag' => true,
                'updated_at' => date("Y/m/d H:i:s")
            ]);

        Cart::instance(Auth::user()->id)->destroy();    

        return view('checkout.success');
    }
}
