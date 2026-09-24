<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;

class BranchController extends Controller
{
    public function edit()
    {
        $branch = Auth::user()->branch ?? Branch::first();
        return view('manager.branch.edit', compact('branch'));
    }

    public function update(Request $request)
    {
        $branch = Auth::user()->branch ?? Branch::first();

        if (!$branch) {
            return back()->with('error', 'Aucune agence/gare associée.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'city' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
        ]);

        $branch->update($validated);

        return back()->with('success', 'Informations du terminal/agence mises à jour avec succès.');
    }
}
