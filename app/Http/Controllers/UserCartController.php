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

    // helper: get or create active receipt for user
    protected function getActiveReceipt($user)
    {
        $receipt = Receipt::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$receipt) {
            $receipt = Receipt::create([
                'user_id' => $user->id,
                'status' => 'active',
            ]);
        }

        return $receipt;
    }

    // GET /user/cart
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

    // POST /user/cart
    public function store(Request $request)
    {
        $user = Auth::user();
        $receipt = $this->getActiveReceipt($user);

        $data = $request->validate([
            'product_id' => 'nullable|integer',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
            'image' => 'nullable|string',
        ]);

        $existing = null;
        if (!empty($data['product_id'])) {
            $existing = $receipt->items()
                ->where('product_id', $data['product_id'])
                ->first();
        }

        if ($existing) {
            $existing->quantity += $data['quantity'];
            $existing->save();
            return response()->json($existing);
        }

        $item = $receipt->items()->create([
            'user_id' => $user->id,
            'product_id' => $data['product_id'] ?? null,
            'name' => $data['name'],
            'price' => $data['price'],
            'quantity' => $data['quantity'],
            'image' => $data['image'] ?? null,
        ]);

        return response()->json($item, 201);
    }

    // PUT /user/cart/{id}
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $receipt = $this->getActiveReceipt($user);
        $item = $receipt->items()->where('id', $id)->firstOrFail();

        $data = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        if ($data['quantity'] <= 0) {
            $item->delete();
            return response()->json(null, 204);
        }

        $item->quantity = $data['quantity'];
        $item->save();

        return response()->json($item);
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
