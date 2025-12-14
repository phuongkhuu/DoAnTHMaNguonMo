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
