<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;

class PurchaseAddressController extends Controller
{
    public function showForm($item_id)
    {
        return view('item.address', compact('item_id'));
    }

    // RequestをAddressRequestに差し替え
    public function submitAddress(AddressRequest $request, $item_id)
    {
        // バリデーション済みデータを取得
        $validated = $request->validated();

        // セッションに保存
        session([
            'purchase_address' => [
                'postal_code' => $validated['postal_code'],
                'address' => $validated['address'],
                'building' => $validated['building'] ?? null,
            ]
        ]);

        return redirect()->route('purchase.show', ['item_id' => $item_id]);
    }
}