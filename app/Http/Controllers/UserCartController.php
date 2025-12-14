<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCart;
use App\Models\Receipt;

class UserCartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

   
    public function index()
    {
        $user = Auth::user();
        $receipt = $this->getActiveReceipt($user);
        $items = $receipt->items()->get();
        return response()->json([
            'receipt_id' => $receipt->id,
            'items' => $items,
        ]);
    }

   
    // DELETE /user/cart/{id}
    public function destroy($id)
    {
        $user = Auth::user();
        $receipt = $this->getActiveReceipt($user);
        $item = $receipt->items()->where('id', $id)->firstOrFail();
        $item->delete();
        return response()->json(null, 204);
    }
}
