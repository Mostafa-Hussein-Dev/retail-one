<?php

namespace App\Http\Controllers;

use App\Models\{Setting, ActivityLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'manager') abort(403);
        return view('settings.index');
    }

    public function store()
    {
        if (auth()->user()->role !== 'manager') abort(403);
        return view('settings.store', ['settings' => Setting::all()->pluck('value', 'key')]);
    }

    public function updateStore(Request $request)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'store_phone' => 'nullable|string|max:50',
            'store_tax_id' => 'nullable|string|max:100',
            'receipt_footer' => 'nullable|string|max:500',
            'store_logo' => 'nullable|image|max:2048',
        ]);

        Setting::set('store_name', $request->store_name);
        Setting::set('store_address', $request->store_address);
        Setting::set('store_phone', $request->store_phone);
        Setting::set('store_tax_id', $request->store_tax_id);
        Setting::set('receipt_footer', $request->receipt_footer);
        Setting::set('receipt_show_logo', $request->receipt_show_logo ? 1 : 0);
        Setting::set('receipt_auto_print', $request->receipt_auto_print ? 1 : 0);

        if ($request->hasFile('store_logo')) {
            $path = $request->file('store_logo')->store('public/logos');
            Setting::set('store_logo', Storage::url($path));
        }

        ActivityLog::log('settings_updated', 'Updated store settings');

        return redirect()->route('settings.store')->with('success', 'تم تحديث إعدادات المتجر بنجاح');
    }

    public function exchangeRate()
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $currentRate = Setting::get('exchange_rate_usd_lbp', 89500);
        $rateHistory = ActivityLog::where('action', 'exchange_rate_updated')->latest()->limit(10)->get();

        return view('settings.exchange-rate', compact('currentRate', 'rateHistory'));
    }

    public function updateExchangeRate(Request $request)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $request->validate(['exchange_rate_usd_lbp' => 'required|numeric|min:1']);

        $oldRate = Setting::get('exchange_rate_usd_lbp');
        $newRate = $request->exchange_rate_usd_lbp;

        Setting::set('exchange_rate_usd_lbp', $newRate);
        ActivityLog::log('exchange_rate_updated', "Changed rate from {$oldRate} to {$newRate} LBP per USD");

        return redirect()->route('settings.exchange-rate')->with('success', 'تم تحديث سعر الصرف بنجاح');
    }

    public function preferences()
    {
        if (auth()->user()->role !== 'manager') abort(403);
        return view('settings.preferences', ['settings' => Setting::all()->pluck('value', 'key')]);
    }

    public function updatePreferences(Request $request)
    {
        if (auth()->user()->role !== 'manager') abort(403);

        $request->validate([
            'low_stock_threshold' => 'required|integer|min:1',
            'pagination_per_page' => 'required|integer|min:5|max:100',
            'session_timeout' => 'required|integer|min:30|max:1440',
            'backup_time' => 'nullable|regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/',
            'backup_retention_days' => 'required|integer|min:7|max:365',
        ]);

        Setting::set('low_stock_threshold', $request->low_stock_threshold);
        Setting::set('pagination_per_page', $request->pagination_per_page);
        Setting::set('session_timeout', $request->session_timeout);
        Setting::set('backup_enabled', $request->backup_enabled ? 1 : 0);
        Setting::set('backup_time', $request->backup_time);
        Setting::set('backup_retention_days', $request->backup_retention_days);

        ActivityLog::log('preferences_updated', 'Updated system preferences');

        return redirect()->route('settings.preferences')->with('success', 'تم تحديث التفضيلات بنجاح');
    }
}
