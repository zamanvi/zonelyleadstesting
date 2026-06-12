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
            'total'           => Lead::count(),
            'new'             => Lead::where('status', 'new')->count(),
            'won'             => Lead::where('status', 'won')->count(),
            'lost'            => Lead::where('status', 'lost')->count(),
            'form'            => Lead::where('source', 'form')->count(),
            'phone'           => Lead::where('source', 'phone')->count(),
            'whatsapp'        => Lead::where('source', 'whatsapp')->count(),
            'email'           => Lead::where('source', 'email')->count(),
            'booking'         => Lead::where('source', 'booking')->count(),
            'paid'            => Lead::whereNotNull('paid_at')->count(),
            'unpaid'          => Lead::whereNull('paid_at')->count(),
            'revenue'         => Lead::whereNotNull('paid_at')->sum('fee'),
            'pending_revenue' => Lead::whereNull('paid_at')->sum('fee'),
        ];

        return view('admin.leads.index', compact('leads', 'stats'));
    }
}
