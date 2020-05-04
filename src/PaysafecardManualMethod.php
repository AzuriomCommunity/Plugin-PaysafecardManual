<?php

namespace Azuriom\Plugin\PaysafecardManual;

use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Cart\Cart;
use Azuriom\Plugin\Shop\Models\Payment;
use Azuriom\Plugin\Shop\Payment\PaymentMethod;
use Illuminate\Http\Request;

class PaysafecardManualMethod extends PaymentMethod
{
    /**
     * The payment method id name.
     *
     * @var string
     */
    protected $id = 'paysafecardmanual';

    /**
     * The payment method display name.
     *
     * @var string
     */
    protected $name = 'Paysafecard Manual';

    public function startPayment(Cart $cart, float $amount, string $currency)
    {
        $payment = $this->createPayment($cart, $amount, $currency);
        return view('paysafecardmanual::paysafecardmanual', ['payment_id' => $payment->id]);
    }

    public function notification(Request $request, ?string $paymentId)
    {
        if (! $request->has('code') || ! $request->has('custom')) {
            return response()->json(['status' => 'error', 'message' => 'No code or no custom']);
        }

        $code = $request->input('code');
        $publicKey = $this->gateway->data['public-key'];
        $privateKey = $this->gateway->data['private-key'];

        if ($privateKey !== $request->input('privateKey')) {
            logger()->warning('[Shop] Dedipass - Invalid private key: '.$request->input('privateKey'));

            return response()->json(['status' => 'error', 'message' => 'Invalid private key']);
        }

        if (Payment::where('payment_id', $code)->where('created_at', '>', now()->subMinute())->exists()) {
            //logger()->warning('[Shop] Dedipass - Payment already completed: '.$code);

            return response()->json(['status' => 'success', 'message' => 'Payment already completed']);
        }

        // TODO Dedipass API is broken when using IPN, so we can't verify the request...
        //$url = "http://api.dedipass.com/v1/pay/?public_key={$publicKey}&private_key=$privateKey&code={$code}";
        //$response = (new Client())->post($url);

        $status = $request->input('status');

        if ($status !== 'success') {
            logger()->warning('[Shop] Dedipass - Invalid status: '.$status);

            return response()->json(['status' => 'error', 'message' => 'Invalid status']);
        }

        $price = $request->input('payout', 0);
        $money = $request->input('virtual_currency', 0);

        $user = User::find($request->input('custom'));

        if ($user === null) {
            return response()->json(['status' => 'error', 'message' => 'Invalid user id']);
        }

        $user->addMoney($money);
        $user->save();

        Payment::forceCreate([
            'user_id' => $user->id,
            'price' => $price,
            'currency' => 'EUR',
            'payment_type' => $this->id,
            'status' => 'DELIVERED',
            'items' => 'Money: '.$money,
            'payment_id' => $code,
            'type' => 'offers',
        ]);

        return response()->json(['status' => 'success']);
    }

    public function success(Request $request)
    {
        return view('shop::payments.success');
    }

    public function view()
    {
        return 'paysafecardmanual::admin.paysafecardmanual';
    }

    public function rules()
    {
        return [
            
        ];
    }

    public function image()
    {
        return asset('plugins/paysafecardmanual/img/paysafe-card.png');
    }

    public function hasFixedAmount()
    {
        return false;
    }
}
