<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Http\Requests\PurchaseRequest;

class PurchaseController extends Controller
{
    public function complete(PurchaseRequest $request, $item_id)
    {

        $user = Auth::user();

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item_id,
            'payment_method' => $request->payment_method,
            'shipping_postal_code' => $request->shipping_postal_code,
            'shipping_address' => $request->shipping_address,
            'shipping_building' => $request->shipping_building,
        ]);

        return redirect('/mypage?page=buy')
        ->with('success', '購入が完了しました')
        ->with('payment_method', $request->payment_method)
        ->with('purchased_item_id', $item_id);
    }

    public function confirm($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        return view('item.purchase', compact('item', 'user', 'item_id'));
    }

    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // セッションに住所があればそちらを優先
        $addressData = session('purchase_address');
        $postalCode = $addressData['postal_code'] ?? $user->postal_code;
        $address = $addressData['address'] ?? $user->address;
        $building = $addressData['building'] ?? $user->building;

        return view('item.purchase', compact('item', 'postalCode', 'address', 'building', 'user'));
    }
}
