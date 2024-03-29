<?php

namespace Azuriom\Plugin\PaysafecardManual\Controllers\Admin;

use Azuriom\Azuriom;
use Azuriom\Support\Discord\DiscordWebhook;
use Azuriom\Support\Discord\Embed;
use Azuriom\Http\Controllers\Controller;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\PaysafecardManual\Models\PendingCode;
use Azuriom\Plugin\Shop\Events\PaymentPaid;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Http\Request;
use Azuriom\Models\ActionLog;

class AdminController extends Controller
{
    /**
     * Show the home admin page of the plugin.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('paysafecardmanual::admin.index', [
            'codes' => PendingCode::all(),
        ]);
    }

    public function accept(Request $request, PendingCode $code)
    {
        $this->validate($request, [
            'price' => 'required|numeric|min:0',
            'money' => 'required|numeric|min:0',
        ]);

        $price = $request->input('price');
        $money = $request->input('money');

        $payment = Payment::create([
            'user_id' => $code->user_id,
            'price' => $price,
            'currency' => currency(),
            'gateway_type' => 'paysafecard_manual',
            'status' => 'completed',
            'transaction_id' => $code->code,
        ]);

        ActionLog::log(trans('paysafecardmanual::messages.status.accepted') . ' (' . $price . ' | ' . $money . ')', $code->user);

        $code->user->addMoney($money);

        $code->forceDelete();

        event(new PaymentPaid($payment));

        $notification = (new AlertNotification(trans('paysafecardmanual::messages.notifications.accepted', [
            'money' => format_money($money),
        ])));

        $code->user->notifications()->create($notification->toArray());

        if (($webhookUrl = setting('shop.webhook')) !== null) {
            $embed = Embed::create()
                ->title(trans('paysafecardmanual::messages.widget.accepted'))
                ->author($code->user->name, null, $code->user->getAvatar())
                ->addField(trans('paysafecardmanual::messages.widget.money'), $price . " " . currency())
                ->addField(trans('paysafecardmanual::messages.widget.amount'), format_money($money))
                ->addField(trans('paysafecardmanual::messages.widget.user'), $code->user->name)
                ->color('#004de6')
                ->footer('Azuriom v' . Azuriom::version())
                ->timestamp(now());

            rescue(function () use ($embed, $webhookUrl) {
                DiscordWebhook::create()->addEmbed($embed)->send($webhookUrl);
            });
        }

        return redirect()->route('paysafecardmanual.admin.index')->with([
            'success' => trans('paysafecardmanual::messages.status.accepted'),
        ]);
    }

    public function refuse(PendingCode $code)
    {
        $code->delete();

        $notification = (new AlertNotification(trans('paysafecardmanual::messages.notifications.refused', [
            'code' => $code->code,
        ])))->level('warning');

        ActionLog::log(trans('paysafecardmanual::messages.status.refused'), $code->user);

        $code->user->notifications()->create($notification->toArray());
        if (($webhookUrl = setting('shop.webhook')) !== null) {
            $embed = Embed::create()
                ->title(trans('paysafecardmanual::messages.widget.refused'))
                ->author($code->user->name, null, $code->user->getAvatar())
                ->addField(trans('paysafecardmanual::messages.widget.user'), $code->user->name)
                ->color('#004de6')
                ->footer('Azuriom v' . Azuriom::version())
                ->timestamp(now());

            rescue(function () use ($embed, $webhookUrl) {
                DiscordWebhook::create()->addEmbed($embed)->send($webhookUrl);
            });
        }

        return redirect()->route('paysafecardmanual.admin.index')->with([
            'success' => trans('paysafecardmanual::messages.status.refused'),
        ]);
    }
}
