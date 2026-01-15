@extends('layouts.app')

@section('content')

    <h1 style="margin-bottom: 2rem;">سعر الصرف (USD/LBP)</h1>

    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
        <!-- Current Rate -->
        <div class="card">
            <div style="padding: 1.5rem; border-bottom: 2px solid #ecf0f1;">
                <h3 style="margin: 0; color: #2c3e50;">السعر الحالي</h3>
            </div>
            <div style="padding: 2rem;">
                <form method="POST">
                    @csrf
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <span style="font-size: 1.1rem; color: #7f8c8d;">1 USD =</span>
                        <input type="number" name="exchange_rate_usd_lbp"
                               value="{{ $currentRate }}"
                               required min="1" step="1"
                               style="flex: 1; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1.1rem;">
                        <span style="font-size: 1.1rem; color: #7f8c8d;">LBP</span>
                    </div>
                    <button type="submit"
                            style="width: 100%; background-color: #1abc9c; color: white; padding: 0.75rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#16a085'"
                            onmouseout="this.style.backgroundColor='#1abc9c'">
                        تحديث السعر
                    </button>
                    @if($rateHistory->first()?->created_at)
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-top: 1rem; text-align: center;">
                        آخر تحديث: {{ $rateHistory->first()->created_at->format('Y-m-d H:i:s') }}
                    </p>
                    @endif
                </form>
            </div>
        </div>

        <!-- Rate History -->
        <div class="card">
            <div style="padding: 1.5rem; border-bottom: 2px solid #ecf0f1;">
                <h3 style="margin: 0; color: #2c3e50;">تاريخ التحديثات</h3>
            </div>
            <div style="padding: 1.5rem;">
                @if($rateHistory->count() > 0)
                    <div style="max-height: 300px; overflow-y: auto;">
                        @foreach($rateHistory as $log)
                        <div style="padding: 0.75rem; border-bottom: 1px solid #ecf0f1; display: flex; flex-direction: column; gap: 0.25rem;">
                            <div style="color: #7f8c8d; font-size: 0.85rem;">{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                            <div style="color: #2c3e50;">{{ $log->description }}</div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; color: #7f8c8d; padding: 2rem;">لا يوجد سجل</div>
                @endif
            </div>
        </div>
    </div>

@endsection
