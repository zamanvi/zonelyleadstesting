<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with('seller')
            ->latest();

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%"));
        }

        $leads = $query->paginate(30)->withQueryString();

        $stats = [
            'total'    => Lead::count(),
            'form'     => Lead::where('source', 'form')->count(),
            'whatsapp' => Lead::where('source', 'whatsapp')->count(),
            'email'    => Lead::where('source', 'email')->count(),
            'paid'     => Lead::whereNotNull('paid_at')->count(),
            'unpaid'   => Lead::whereNull('paid_at')->count(),
        ];

        return view('admin.leads.index', compact('leads', 'stats'));
    }
}
