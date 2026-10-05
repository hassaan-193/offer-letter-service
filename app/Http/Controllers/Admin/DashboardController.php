<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfferLetter;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total'             => OfferLetter::count(),
            'drafts'            => OfferLetter::where('status', 'draft')->count(),
            'published'         => OfferLetter::where('status', 'published')->count(),
            'viewed'            => OfferLetter::where('status', 'viewed')->count(),
            'pending_signature' => OfferLetter::where('status', 'pending_signature')->count(),
            'signed'            => OfferLetter::where('status', 'signed')->count(),
            'expired'           => OfferLetter::where('status', 'expired')->count(),
            'revoked'           => OfferLetter::where('status', 'revoked')->count(),
        ];

        $recentOffers = OfferLetter::with('admin')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOffers'));
    }
}
