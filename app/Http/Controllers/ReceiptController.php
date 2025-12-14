<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;
use App\Services\MomoPaymentService;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET /receipts/active
    public function active()
    {
        $user = Auth::user();
        $receipt = Receipt::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('items')
            ->first();

        return response()->json($receipt);
    }

    public function index()
    {
        $user = Auth::user();

        $receipts = Receipt::where('user_id', $user->id)
            ->where('total', '>', 0)
            ->with(['user', 'items.product'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($receipts);
    }

    // Local checkout (no gateway)
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $receipt = Receipt::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('items')
            ->firstOrFail();

        DB::transaction(function () use ($receipt) {
            $receipt->status = 'completed';
            $receipt->save();
        });

        return response()->json(['message' => 'Checkout successful', 'receipt_id' => $receipt->id]);
    }

    // MoMo checkout
    public function checkoutMoMo(Request $request, MomoPaymentService $momo)
    {
        $receiptId = $request->input('receipt_id');
        $receipt = Receipt::findOrFail($receiptId);

        $orderId = 'R' . $receipt->id . '_' . time();
        $amount  = (int) $receipt->total;

        $res = $momo->createPayment($amount, $orderId, "Thanh toán hóa đơn #{$receipt->id}");

        $receipt->update([
            'momo_order_id' => $orderId,
            'payment_gateway' => 'momo',
            'payment_state' => 'initiated',
        ]);

        return response()->json(['payUrl' => $res['payUrl'] ?? null]);
    }

    // MoMo return
    public function momoReturn(Request $request, MomoPaymentService $momo)
    {
        $params = $request->all();
        if (!$momo->verifySignature($params)) {
            return redirect('/checkout?status=failed');
        }

        $receipt = Receipt::where('momo_order_id', $params['orderId'])->first();
        if (!$receipt) {
            return redirect('/checkout?status=failed');
        }

        if ($params['resultCode'] == 0) {
            $receipt->update([
                'status' => 'paid',
                'payment_state' => 'succeeded',
                'paid_at' => now(),
                'transaction_id' => $params['transId'] ?? null,
            ]);
            return redirect('/checkout-success');
        } else {
            $receipt->update(['payment_state' => 'failed']);
            return redirect('/checkout?status=failed');
        }
    }

    // MoMo IPN (server callback)
    public function momoIpn(Request $request, MomoPaymentService $momo)
    {
        $params = $request->all();
        if (!$momo->verifySignature($params)) {
            return response()->json(['result' => 1, 'message' => 'invalid signature'], 400);
        }

        $receipt = Receipt::where('momo_order_id', $params['orderId'])->first();
        if (!$receipt) {
            return response()->json(['result' => 1, 'message' => 'unknown order'], 404);
        }

        if ($params['resultCode'] == 0) {
            $receipt->update([
                'status' => 'paid',
                'payment_state' => 'succeeded',
                'paid_at' => now(),
                'transaction_id' => $params['transId'] ?? null,
            ]);
            return response()->json(['result' => 0, 'message' => 'ok']);
        }

        $receipt->update(['payment_state' => 'failed']);
        return response()->json(['result' => 0, 'message' => 'failed handled']);
    }
    public function destroy(Receipt $receipt)
    {
        $user = Auth::user();

        DB::transaction(function () use ($receipt) {
            $receipt->items()->delete();
            $receipt->delete();
        });

        return response()->json(['message' => 'Đã xóa hóa đơn']);
    }
}
