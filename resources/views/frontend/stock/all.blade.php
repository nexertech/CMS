@extends('frontend.layouts.app')

@section('title', 'Consumption Record (All Stock)')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Section -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-white">
                Consumption Record - {{ $selectedYear == 'all_time' ? 'All Time' : $selectedYear }}
            </h1>
            <p class="text-white" style="font-size: 10px; color: white !important;">
                Monthly stock consumption and inventory balance for current selection.
            </p>
        </div>
        <div class="flex items-center gap-2 no-print">

            <form action="{{ route('frontend.stock.all') }}" method="GET" id="yearFilterForm" class="m-0">
                <select name="year" onchange="document.getElementById('yearFilterForm').submit()" class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs font-bold bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all cursor-pointer">
                    <option value="all_time" {{ $selectedYear == 'all_time' ? 'selected' : '' }}>All Time</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>Year {{ $year }}</option>
                    @endforeach
                </select>
            </form>
            <button onclick="exportTableToExcel('fullStockTable', 'all_stock_consumption_report_{{ $selectedYear }}')" class="inline-flex items-center px-3 py-1.5 border border-green-600 rounded-lg shadow-sm text-xs font-bold text-white bg-green-600 hover:bg-green-700 transition-all">
                <i data-feather="download" class="mr-1.5" style="width: 14px; height: 14px;"></i>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Stats Summary Row -->
    @php
        $grandTotalReceived = 0;
        $grandTotalUsed = 0;
        $grandBalance = 0;
        foreach($stockConsumptionData as $data) {
            $grandTotalReceived += $data['total_received'];
            $grandTotalUsed += $data['total_used'];
            $grandBalance += $data['current_stock'];
        }
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 italic">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-blue-50 text-blue-600 mr-3">
                    <i data-feather="package" style="width: 18px; height: 18px;"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Total Received</p>
                    <h3 class="text-xl font-bold text-gray-900">{{ number_format($grandTotalReceived) }}</h3>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 italic">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-red-50 text-red-600 mr-3">
                    <i data-feather="trending-up" style="width: 18px; height: 18px;"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Total Issued</p>
                    <h3 class="text-xl font-bold text-gray-900">{{ number_format($grandTotalUsed) }}</h3>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 italic">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-green-50 text-green-600 mr-3">
                    <i data-feather="check-circle" style="width: 18px; height: 18px;"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Stock Balance</p>
                    <h3 class="text-xl font-bold text-gray-900">{{ number_format($grandBalance) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Table Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="fullStockTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200 sticky left-0 bg-gray-50 z-10 w-64">
                            Item Name ({{ $selectedYear == 'all_time' ? 'All Time' : $selectedYear }})
                        </th>
                        @foreach($monthLabels as $month)
                            <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                {{ $month }}
                            </th>
                        @endforeach
                        <th class="px-3 py-2 text-center text-[10px] font-bold text-blue-700 uppercase tracking-wider border-r border-gray-200 bg-blue-50">Total Received</th>
                        <th class="px-3 py-2 text-center text-[10px] font-bold text-red-600 uppercase tracking-wider border-r border-gray-200 bg-red-50">Total Issued</th>
                        <th class="px-3 py-2 text-center text-[10px] font-bold text-green-600 uppercase tracking-wider bg-green-50">Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $monthTotalsReceived = array_fill_keys($monthLabels, 0);
                    @endphp
                    @forelse($stockConsumptionData as $itemName => $data)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-1.5 whitespace-nowrap text-[12px] font-bold text-gray-900 border-r border-gray-200 sticky left-0 bg-white z-10 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
                                {{ $itemName }}
                            </td>
                            @foreach($monthLabels as $month)
                                @php
                                    $receivedQty = $data['monthly_received_data'][$month] ?? 0;
                                    $monthTotalsReceived[$month] += $receivedQty;
                                @endphp
                                <td class="px-2 py-1.5 whitespace-nowrap text-[12px] text-center border-r border-gray-200 {{ $receivedQty > 0 ? 'text-blue-600 font-semibold bg-blue-50/30' : 'text-gray-400' }}">
                                    {{ $receivedQty > 0 ? $receivedQty : '-' }}
                                </td>
                            @endforeach
                            <td class="px-3 py-1.5 whitespace-nowrap text-[12px] text-center font-bold text-blue-700 border-r border-gray-200 bg-blue-50/50">
                                {{ number_format($data['total_received']) }}
                            </td>
                            <td class="px-3 py-1.5 whitespace-nowrap text-[12px] text-center font-bold text-red-600 border-r border-gray-200 bg-red-50/50">
                                {{ number_format($data['total_used']) }}
                            </td>
                            <td class="px-3 py-1.5 whitespace-nowrap text-[12px] text-center font-bold text-green-600 bg-green-50/50">
                                {{ number_format($data['current_stock']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($monthLabels) + 4 }}" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i data-feather="inbox" class="mb-2 opacity-20" style="width: 48px; height: 48px;"></i>
                                    <p>No inventory consumption records found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!empty($stockConsumptionData))
                <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-300">
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap text-[12px] text-gray-900 border-r border-gray-200 sticky left-0 bg-gray-100 z-10">
                            GRAND TOTAL
                        </td>
                        @foreach($monthLabels as $month)
                            <td class="px-2 py-2 whitespace-nowrap text-[12px] text-center text-blue-800 border-r border-gray-200">
                                {{ number_format($monthTotalsReceived[$month]) }}
                            </td>
                        @endforeach
                        <td class="px-3 py-2 whitespace-nowrap text-[12px] text-center text-blue-800 border-r border-gray-200 bg-blue-100/50">
                            {{ number_format($grandTotalReceived) }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-[12px] text-center text-red-800 border-r border-gray-200 bg-red-100/50">
                            {{ number_format($grandTotalUsed) }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-[12px] text-center text-green-800 bg-green-100/50">
                            {{ number_format($grandBalance) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
<style>
    body {
        padding-top: 120px !important; /* Ensure content is below absolute navbar */
        background: url('{{ asset('assests/Background.jpg') }}') no-repeat center center/cover;
        background-attachment: fixed;
        position: relative;
        min-height: 100vh;
    }

    /* Dark overlay for better readability */
    body::after {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0, 31, 63, 0.3) 0%, rgba(0, 51, 102, 0.35) 50%, rgba(0, 77, 153, 0.3) 100%);
        z-index: -1; /* Behind content but over background */
        pointer-events: none;
    }
    
    /* Ensure content is above the overlay */
    .container-fluid {
        position: relative;
        z-index: 10;
    }

    main {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    /* Aggressive Compactness */
    th, td {
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
        height: auto !important;
    }

    @media print {
        .no-print { display: none !important; }
        body { background: white !important; }
        .container-fluid { padding: 0 !important; }
        .shadow-sm { shadow: none !important; }
        table { border: 1px solid #ddd !important; }
        th, td { border: 1px solid #ddd !important; padding: 4px !important; font-size: 10px !important; }
    }
    
    /* Custom Scrollbar for the table */
    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableID, filename = ''){
        var table = document.getElementById(tableID);
        var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet JS"});
        XLSX.writeFile(wb, filename + '.xlsx');
    }
    
    // Refresh feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
@endpush

@endsection
