<?php

namespace App\Http\Controllers;

use App\Services\SignatoryStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SignatoryController extends Controller
{
    public function update(Request $request, SignatoryStore $store): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', 'max:160'],
        ]);

        $result = $store->update(trim($data['name']), trim($data['role']));

        return redirect()->route('dashboard')
            ->withFragment('signatory')
            ->with($result['ok'] ? 'status' : 'error', $result['message']);
    }
}
