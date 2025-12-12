@extends('frontend.layouts.app')

@section('title', 'Dashboard UI')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <style>
        .header-bg,
        div.header-bg,
        .relative.bg-cover.bg-center.header-bg {
            height: 40px !important;
            min-height: 400px !important;
            max-height: none !important;
        }

        /* Browser compatibility for text-size-adjust */
        html,
        body {
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }

        /* Matte finish for right stats boxes */
        .w-96.grid>div {
            position: relative;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15), 0 2px 8px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            filter: saturate(0.9) brightness(0.95);
            transition: all 0.3s ease;
        }

        .w-96.grid>div::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.08);
            border-radius: inherit;
            pointer-events: none;
            z-index: 1;
        }

        .w-96.grid>div::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(0, 0, 0, 0.02) 100%);
            border-radius: inherit;
            pointer-events: none;
            z-index: 1;
        }

        .w-96.grid>div>* {
            position: relative;
            z-index: 2;
        }

        .w-96.grid>div:hover {
            opacity: 0.95;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2), 0 4px 12px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.15) !important;
            transform: translateY(-2px);
            filter: saturate(0.95) brightness(0.98);
        }

        /* Reduce gradient intensity for matte look */
        .w-96.grid>div[style*="linear-gradient"] {
            background-blend-mode: overlay !important;
        }

        /* Matte finish for Complaints by Status chart */
        .complaints-by-status-chart {
            position: relative;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            filter: saturate(0.9) brightness(0.97);
            transition: all 0.3s ease;
        }

        .complaints-by-status-chart::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.06);
            border-radius: inherit;
            pointer-events: none;
            z-index: 1;
        }

        .complaints-by-status-chart::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(0, 0, 0, 0.02) 100%);
            border-radius: inherit;
            pointer-events: none;
            z-index: 1;
        }

        .complaints-by-status-chart>* {
            position: relative;
            z-index: 2;
        }

        .complaints-by-status-chart:hover {
            opacity: 0.96;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
            transform: translateY(-1px);
            filter: saturate(0.93) brightness(0.99);
        }

        /* Matte finish for Monthly Complaints chart */
        .monthly-complaints-chart {
            position: relative;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            filter: saturate(0.9) brightness(0.97);
            transition: all 0.3s ease;
        }

        .monthly-complaints-chart::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.06);
            border-radius: inherit;
            pointer-events: none;
            z-index: 1;
        }

        .monthly-complaints-chart::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.03) 0%, rgba(0, 0, 0, 0.02) 100%);
            border-radius: inherit;
            pointer-events: none;
            z-index: 1;
        }

        .monthly-complaints-chart>* {
            position: relative;
            z-index: 2;
        }

        .monthly-complaints-chart:hover {
            opacity: 0.96;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
            transform: translateY(-1px);
            filter: saturate(0.93) brightness(0.99);
        }

        .stock {
            margin-left: 5%;
            margin-right: 5%;
        }
    </style>
@endpush

@section('content')

    <!-- Header Background -->
    <div class="relative bg-cover bg-center header-bg"
        style="background-image: url('{{ asset('assests/Background.jpg') }}');">

        <div class="absolute inset-0 bg-blue-900 bg-opacity-40"></div>
        <!-- Logo -->
        <!-- <div class="absolute top-9 left-1/2 transform -translate-x-1/2 text-white text-center">
                        <img src="{{ asset('assests/logo.png') }}" class="h-28 mx-auto mb-2" alt="Pakistan Navy Logo" onerror="this.src='{{ asset('assests/logo.png') }}'" />
                    </div> -->
        <!-- Filters -->
        <div class="absolute top-44 p-2 flex items-end justify-start gap-2"
            style="left: 5%; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(3px); border-radius: 4px; white-space: nowrap; width: -webkit-max-content; width: -moz-max-content; width: max-content; overflow: visible;">
            <div style="flex: 0 0 auto;">
                <label for="filterCMES" class="block text-white mb-1"
                    style="font-size: 1.2rem; font-weight: 700;">CMES</label>
                <select id="filterCMES" name="cmes_id" class="p-1.5 border filter-select"
                    style="font-size: 1rem; width: 200px; border-radius: 4px; font-weight: bold;" aria-label="Select CMES"
                    title="Select CMES">
                    <option value="">Select CMES</option>
                    @if(isset($cmesList) && $cmesList->count() > 0)
                        @foreach($cmesList as $cme)
                            <option value="{{ $cme->id }}" {{ (isset($cmesId) && $cmesId == $cme->id) ? 'selected' : '' }}>
                                {{ $cme->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div style="flex: 0 0 auto;">
                <label for="filterCity" class="block text-white mb-1"
                    style="font-size: 1.2rem; font-weight: 700;">GE</label>
                <select id="filterCity" name="city_id" class="p-1.5 border filter-select"
                    style="font-size: 1rem; width: 200px; border-radius: 4px; font-weight: bold;" aria-label="Select GE"
                    title="Select GE">
                    <option value="">Select GE</option>
                    @foreach($geGroups as $ge)
                        <option value="{{ $ge->id }}" {{ $cityId == $ge->id ? 'selected' : '' }}>{{ $ge->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 0 0 auto;">
                <label for="filterSector" class="block text-white mb-1" style="font-size: 1.2rem; font-weight: 700;">GE
                    Nodes</label>
                <select id="filterSector" name="sector_id" class="p-1.5 border filter-select"
                    style="font-size: 1rem; width: 200px; border-radius: 4px; font-weight: bold;"
                    aria-label="Select GE Nodes" title="Select GE Nodes">
                    <option value="">Select GE Nodes</option>
                    @foreach($geNodes as $node)
                        <option value="{{ $node->id }}" {{ $sectorId == $node->id ? 'selected' : '' }}>{{ $node->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 0 0 auto;">
                <label for="filterCategory" class="block text-white mb-1"
                    style="font-size: 1.2rem; font-weight: 700;">Complaints Category</label>
                <select id="filterCategory" name="category" class="p-1.5 border filter-select"
                    style="font-size: 1rem; width: 200px; border-radius: 4px; font-weight: bold;"
                    aria-label="Select Complaints Category" title="Select Complaints Category">
                    <option value="all">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ $category == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 0 0 auto;">
                <label for="filterStatus" class="block text-white mb-1"
                    style="font-size: 1.2rem; font-weight: 700;">Complaints Status</label>
                <select id="filterStatus" name="status" class="p-1.5 border filter-select"
                    style="font-size: 1rem; width: 200px; border-radius: 4px; font-weight: bold;"
                    aria-label="Select Complaints Status" title="Select Complaints Status">
                    <option value="all">Select Status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 0 0 auto;">
                <label for="filterDateRange" class="block text-white mb-1" style="font-size: 1.2rem; font-weight: 700;">Date
                    Range</label>
                <select id="filterDateRange" name="date_range" class="p-1.5 border filter-select"
                    style="font-size: 1rem; width: 200px; border-radius: 4px; font-weight: bold;"
                    aria-label="Select Date Range" title="Select Date Range">
                    <option value="">Select Date Range</option>
                    <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="this_week" {{ $dateRange == 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="last_week" {{ $dateRange == 'last_week' ? 'selected' : '' }}>Last Week</option>
                    <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_6_months" {{ $dateRange == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                </select>
            </div>
            <div class="flex items-center" style="flex: 0 0 auto; min-width: 0;">
                <label class="block text-xs font-bold text-gray-700 mb-1"
                    style="opacity: 0; height: 0; margin: 0;">&nbsp;</label>
                <button id="resetFilters"
                    class="px-3 py-1.5 text-sm border bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold whitespace-nowrap"
                    style="font-size: 1rem; padding: 0.5rem 1.25rem; border-radius: 4px;">Reset</button>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="mx-auto mb-8" style="max-width:90%; margin-top: -8rem; position: relative; z-index: 10;">
        <div class="flex gap-6">
            <!-- Left Graphs Section -->
            <div class="flex-1 space-y-6" style="background: white; padding: 1rem 1.5rem; border-radius: 12px;">
                <!-- Monthly Complaints and TVRR Complaints Row -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Monthly Complaints -->
                    <div class="bg-white rounded-xl shadow monthly-complaints-chart"
                        style="position: relative; padding: 0.5rem;">
                        <h2 class="text-xl font-semibold mb-2">Monthly Complaints (2025)</h2>
                        <div class="h-60">
                            <canvas id="monthlyComplaintsChart"></canvas>
                        </div>
                    </div>
                    <!-- Complaints by Status -->
                    <div class="bg-white rounded-xl shadow complaints-by-status-chart"
                        style="position: relative; padding: 0.5rem;">
                        <h2 class="text-xl font-semibold mb-2">Complaints by Status</h2>
                        <div class="h-64 w-full">
                            <canvas id="complaintsByStatusChart"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Complaint Resolution Trend -->
                <div class="bg-white p-6 rounded-xl shadow">
                    <h2 class="text-xl font-semibold mb-4">Complaint Resolution Trend (2025)</h2>
                    <div class="h-96">
                        <canvas id="resolutionTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Right Stats Boxes Section -->
            <div class="w-96 grid grid-cols-2 gap-3"
                style="background: white; padding: 2rem 3rem; border-radius: 12px; align-self: start;">
                <!-- Total Complaints (First) -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-total-complaints" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['total_complaints'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Total Complaints</span>
                </div>
                <!-- In Progress -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #ec5454 0%, #b13030 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-in-progress" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['in_progress'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">In Progress</span>
                </div>
                <!-- Addressed -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #475569 0%, #334155 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-addressed" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['addressed'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Addressed</span>
                </div>
                <!-- Assigned Complaints -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-assigned" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['assigned'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Assigned</span>
                </div>
                <!-- Work Performa -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg,rgb(69, 20, 247) 0%, #7c3aed 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-work-performa" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['work_performa'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Work Performa</span>
                </div>
                <!-- Maintenance Performa -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-maint-performa" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['maint_performa'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Maintenance Performa</span>
                </div>
                <!-- Un Authorized -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-un-authorized" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['un_authorized'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Un Authorized</span>
                </div>
                <!-- Product N/A -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #0deb7cff 0%, #22995dff 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-product" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['product'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Product N/A</span>
                </div>

                <!-- Pertains to GE/Const/Isld -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-pertains-ge" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['pertains_to_ge_const_isld'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Pertains to
                        GE/Const/Isld</span>
                </div>

                <!-- Barak Damages -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #808000 0%, #808000 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-barak-damages" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['barak_damages'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Barrak Damages</span>
                </div>

                <!-- Overdue Complaints -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-overdue-complaints" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['overdue_complaints'] ?? 0 }}</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Overdue Complaints</span>
                </div>
                <!-- Resolution Rate -->
                <div class="text-white rounded-xl text-center font-bold flex flex-col items-center justify-start"
                    style="background: linear-gradient(135deg, #04523eff 0%, #04523eff 100%); min-height: 120px; padding: 1rem 0.75rem;">
                    <span id="stat-resolution-rate" class="text-3xl mb-1 font-bold"
                        style="line-height: 1.2; font-weight: 700;">{{ $stats['resolution_rate'] ?? 0 }}%</span>
                    <span class="text-sm font-bold" style="line-height: 1.2; font-weight: 700;">Resolution Rate</span>
                </div>

            </div>
        </div>


        <!-- CME Complaints Graph Row -->
        <!-- Graphs Row -->
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- CME/GE Graph -->
            <div class="bg-white rounded-xl shadow monthly-complaints-chart" style="position: relative; padding: 1rem;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">
                        @if($isCmeUser)
                            Total Complaints by GE
                        @elseif($isGeUser)
                            Total Complaints by GE Node
                        @else
                            Total Complaints by CMES
                        @endif
                    </h2>
                    <select id="cmeGraphFilter"
                        class="p-1.5 border rounded text-sm font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select</option>
                        <option value="this_month">This Month</option>
                        <option value="last_6_months">Last 6 Months</option>
                        <option value="this_year">This Year</option>
                        <option value="last_year">Last Year</option>
                    </select>
                </div>
                <div class="h-80 w-full">
                    <canvas id="cmeComplaintsChart"></canvas>
                </div>
            </div>

            <!-- Employee Performance Graph -->
            <div class="bg-white rounded-xl shadow" style="position: relative; padding: 1rem;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Employee Performance</h2>
                </div>
                <div class="h-80 w-full">
                    <canvas id="employeePerformanceChart"></canvas>
                </div>
            </div>
        </div>


        <!-- Top Categories by Usage Chart -->
        <div class="mt-6 bg-white rounded-xl shadow monthly-complaints-chart" style="position: relative; padding: 1rem;">
            <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Stock Consumption by Category</h2>
                    <select id="categoryGraphFilter"
                        class="p-1.5 border rounded text-sm font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Time</option>
                        <option value="this_month">This Month</option>
                        <option value="last_6_months">Last 6 Months</option>
                        <option value="this_year">This Year</option>
                        <option value="last_year">Last Year</option>
                    </select>
                </div>
                <div class="h-80 w-full">
                    <canvas id="categoryUsageChart"></canvas>
                </div>
            </div>

            <!-- Monthly Performance Table -->
            <div id="monthlyPerformanceReport" class="mt-8 bg-white rounded-xl shadow overflow-hidden">
                <!-- Print-only heading -->
                <div class="print-only" style="display: none;">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        @if($isCmeUser)
                            Monthly Performance Report of GE
                        @elseif($isGeUser)
                            Monthly Performance Report of Node
                        @elseif($isNodeUser)
                            Monthly Performance Report of Node
                        @else
                            Monthly Performance Report of CMES
                        @endif
                    </h2>
                </div>
                <div class="p-6 border-b border-gray-200 flex justify-between items-center no-print">
                    <h2 class="text-xl font-semibold text-gray-800">
                        @if($isCmeUser)
                            Monthly Performance Report of GE
                        @elseif($isGeUser)
                            Monthly Performance Report of Nodes
                        @elseif($isNodeUser)
                            Monthly Performance Report of Nodes
                        @else
                            Monthly Performance Report of CMES
                        @endif
                    </h2>
                    <div class="flex space-x-2">
                        <button onclick="printSection('monthlyPerformanceReport')"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                            <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z" />
                            </svg>
                            Print PDF
                        </button>
                        <button onclick="exportTableToExcel('monthlyPerformanceTable', 'monthly_performance_report')"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                            <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z" />
                            </svg>
                            Excel
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="monthlyPerformanceTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th rowspan="2"
                                    class="px-4 py-1 text-left text-xs font-extrabold text-gray-900 uppercase tracking-wider border-r border-gray-200 sticky left-0 bg-gray-50 z-10">
                                    Month</th>
                                @foreach($tableEntities as $entity)
                                    <th colspan="2"
                                        class="px-2 py-1 text-center text-xs font-extrabold text-gray-900 uppercase tracking-wider border-r border-gray-200">
                                        {{ $entity->name }}
                                    </th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($tableEntities as $entity)
                                    <th
                                        class="px-2 py-1 text-center text-xs font-bold text-gray-900 uppercase tracking-wider border-r border-gray-200">
                                        Total</th>
                                    <th
                                        class="px-2 py-1 text-center text-xs font-bold text-gray-900 uppercase tracking-wider border-r border-gray-200">
                                        Addressed</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $columnTotals = [];
                                foreach ($tableEntities as $entity) {
                                    $columnTotals[$entity->name]['total'] = 0;
                                    $columnTotals[$entity->name]['resolved'] = 0;
                                }
                            @endphp
                            @foreach($monthlyTableData as $month => $data)
                                <tr class="hover:bg-gray-50">
                                    <td
                                        class="px-4 py-1 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200 sticky left-0 bg-white z-10">
                                        {{ $month }}
                                    </td>
                                    @foreach($tableEntities as $entity)
                                        @php
                                            $t = $data[$entity->name]['total'] ?? 0;
                                            $r = $data[$entity->name]['resolved'] ?? 0;
                                            $columnTotals[$entity->name]['total'] += $t;
                                            $columnTotals[$entity->name]['resolved'] += $r;
                                        @endphp
                                        <td
                                            class="px-2 py-1 whitespace-nowrap text-sm text-center text-gray-900 border-r border-gray-200">
                                            {{ $t }}
                                        </td>
                                        <td
                                            class="px-2 py-1 whitespace-nowrap text-sm text-center text-green-600 font-medium border-r border-gray-200">
                                            {{ $r }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-bold">
                            <tr>
                                <td
                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200 sticky left-0 bg-gray-100 z-10">
                                    Total</td>
                                @foreach($tableEntities as $entity)
                                    <td
                                        class="px-2 py-3 whitespace-nowrap text-sm text-center text-gray-900 border-r border-gray-200">
                                        {{ $columnTotals[$entity->name]['total'] }}
                                    </td>
                                    <td
                                        class="px-2 py-3 whitespace-nowrap text-sm text-center text-green-600 border-r border-gray-200">
                                        {{ $columnTotals[$entity->name]['resolved'] }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        <!-- Stock Consumption Table -->
                <div id="stockConsumptionReport" class="mt-8 bg-white rounded-xl shadow overflow-hidden">
                    <!-- Print-only heading -->
                    <div class="print-only" style="display: none;">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Stock Consumption Report</h2>
                    </div>
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center no-print">
                        <h2 class="text-xl font-semibold text-gray-800">Stock Consumption Report</h2>
                        <div class="flex space-x-2">
                            <button onclick="printSection('stockConsumptionReport')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/></svg>
                                Print PDF
                            </button>
                            <button onclick="exportTableToExcel('stockConsumptionTable', 'stock_consumption_report')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/></svg>
                                Excel
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table id="stockConsumptionTable" class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-1 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200 sticky left-0 bg-gray-50 z-10">Item Name</th>
                                    @foreach($monthLabels as $month)
                                        <th class="px-4 py-1 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                            {{ substr($month, 0, 3) }} <!-- Show Jan, Feb etc -->
                                        </th>
                                    @endforeach
                                    <th class="px-4 py-1 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200">Total Received</th>
                                    <th class="px-4 py-1 text-center text-xs font-bold text-red-600 uppercase tracking-wider border-r border-gray-200">Total Used</th>
                                    <th class="px-4 py-1 text-center text-xs font-bold text-green-600 uppercase tracking-wider">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $stockMonthTotalsReceived = array_fill_keys($monthLabels, 0);
                                    $grandTotalReceived = 0;
                                    $grandTotalUsed = 0;
                                    $grandBalance = 0;
                                @endphp
                                @foreach($stockConsumptionData as $itemName => $data)
                                    @php
                                        $grandTotalReceived += $data['total_received'];
                                        $grandTotalUsed += $data['total_used'];
                                        $grandBalance += $data['current_stock'];
                                    @endphp
                                    <!-- Stock Received Row -->
                                    <tr class="hover:bg-blue-50">
                                        <td class="px-6 py-1 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200 sticky left-0 bg-white z-10">
                                            {{ $itemName }}
                                        </td>
                                        @foreach($monthLabels as $month)
                                            @php
                                                $receivedQty = $data['monthly_received_data'][$month] ?? 0;
                                                $stockMonthTotalsReceived[$month] += $receivedQty;
                                            @endphp
                                            <td class="px-4 py-1 whitespace-nowrap text-sm text-center text-blue-600 font-semibold border-r border-gray-200" style="background-color: #eff6ff;">
                                                {{ $receivedQty > 0 ? $receivedQty : '-' }}
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-1 whitespace-nowrap text-sm text-center font-bold text-gray-700 border-r border-gray-200">
                                            {{ $data['total_received'] }}
                                        </td>
                                        <td class="px-4 py-1 whitespace-nowrap text-sm text-center font-bold text-red-600 border-r border-gray-200">
                                            {{ $data['total_used'] }}
                                        </td>
                                        <td class="px-4 py-1 whitespace-nowrap text-sm text-center font-bold text-green-600">
                                            {{ $data['current_stock'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 font-bold">
                                <tr class="border-t-2 border-gray-400">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200 sticky left-0 bg-gray-100 z-10">
                                        Total
                                    </td>
                                    @foreach($monthLabels as $month)
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-blue-600 border-r border-gray-200">
                                            {{ $stockMonthTotalsReceived[$month] }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border-r border-gray-200">
                                            {{ $grandTotalReceived }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-red-600 border-r border-gray-200">
                                            {{ $grandTotalUsed }}
                                        </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-green-600">
                                        {{ $grandBalance }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer -->

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
    // Register the datalabels plugin
    Chart.register(ChartDataLabels);

    document.addEventListener('DOMContentLoaded', function() {
        // Chart data from backend
        const monthlyData = @json($monthlyComplaints ?? []);
        @php
            $defaultMonthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            // Ensure we have 12 months of data
            $monthlyComplaintsData = $monthlyComplaints ?? [];
            $monthlyResolvedData = $resolvedVsEdData ?? [];

            // Pad arrays to ensure 12 months
            while (count($monthlyComplaintsData) < 12) {
                $monthlyComplaintsData[] = 0;
            }
            while (count($monthlyResolvedData) < 12) {
                $monthlyResolvedData[] = 0;
            }

            // Take only first 12 months
            $monthlyComplaintsData = array_slice($monthlyComplaintsData, 0, 12);
            $monthlyResolvedData = array_slice($monthlyResolvedData, 0, 12);

            // Ensure other arrays are also padded/sliced if they come from backend (though controller logic handles 12 months loop)
            // But for safety in blade if variables are missing:
            $unauthorizedData = $unauthorizedData ?? array_fill(0, 12, 0);
            $performaData = $performaData ?? array_fill(0, 12, 0);
        @endphp
        const monthLabels = @json($monthLabels ?? $defaultMonthLabels);
        const monthlyComplaintsReceived = @json($monthlyComplaintsData);
        const monthlyComplaintsResolved = @json($monthlyResolvedData);
        let complaintsByStatus = @json($complaintsByStatus ?? []);
        const resolvedVsEdData = @json($resolvedVsEdData ?? []);
        const recentEdData = @json($recentEdData ?? []);
        const yearTdData = @json($yearTdData ?? []);
        const unauthorizedData = @json($unauthorizedData ?? []);
        const performaData = @json($performaData ?? []);

        // Monthly Complaints Chart (Grouped Bar Chart)
        const ctx = document.getElementById('monthlyComplaintsChart').getContext('2d');
        const monthlyComplaintsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Total Complaints',
                    data: monthlyComplaintsReceived,
                    backgroundColor: '#3B82F6', // Blue
                    borderRadius: 4,
                    borderSkipped: false,
                }, {
                    label: 'Addressed Complaints',
                    data: resolvedVsEdData,
                    backgroundColor: '#22c55e', // Green
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 12
                        }
                    },
                    datalabels: {
                        color: '#ffffff',
                        font: {
                            weight: 'bold',
                            size: 11
                        },
                        anchor: 'center',
                        align: 'center',
                        formatter: function(value) {
                            return value;
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: 'bold',
                                family: 'Arial, sans-serif'
                            },
                            color: '#1f2937'
                        }
                    }
                }
            }
        });

        // Complaint Resolution Trend Chart (Line Chart)
        const ctx2 = document.getElementById('resolutionTrendChart').getContext('2d');
        const resolutionTrendChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [
                    {
                        label: 'Complaints Received',
                        data: monthlyComplaintsReceived,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Complaints Addressed',
                        data: monthlyComplaintsResolved,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#22c55e',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Un Authorized Barrak Damages',
                        data: unauthorizedData,
                        borderColor: '#808000',
                        backgroundColor: 'rgba(128, 128, 0, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#808000',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Performa',
                        data: performaData,
                        borderColor: '#eab308',
                        backgroundColor: 'rgba(234, 179, 8, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#eab308',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        bottom: 50 // Increased padding to bottom
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 13,
                                weight: 'bold',
                                family: 'Arial, sans-serif'
                            },
                            padding: 8,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 12
                        },
                        mode: 'index',
                        intersect: false
                    },
                    datalabels: {
                        display: 'auto', // Hides labels that overlap
                        clamp: true, // Keeps labels within chart area
                        anchor: function(context) {
                            // Alternate anchor based on dataset index to reduce collision
                            return context.datasetIndex % 2 === 0 ? 'end' : 'start';
                        },
                        align: function(context) {
                            // Alternate alignment based on dataset index
                            return context.datasetIndex % 2 === 0 ? 'end' : 'start';
                        },
                        offset: 4,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        borderRadius: 4,
                        borderWidth: 1,
                        borderColor: function(context) {
                            return context.dataset.borderColor;
                        },
                        padding: {
                            top: 2,
                            bottom: 2,
                            left: 4,
                            right: 4
                        },
                        font: {
                            size: 10, // Slightly smaller font
                            weight: 'bold',
                            family: 'Arial, sans-serif'
                        },
                        color: function(context) {
                            return context.dataset.borderColor;
                        },
                        formatter: function(value) {
                            return value;
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 20, // Increased padding between chart and labels
                            font: {
                                size: 11,
                                weight: 'bold',
                                family: 'Arial, sans-serif'
                            },
                            color: '#1f2937'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });

        // CME Complaints Chart (Line Chart)
        const cmeLabels = @json($cmeGraphLabels ?? []);
        const cmeData = @json($cmeGraphData ?? []);
        const cmeResolvedData = @json($cmeResolvedData ?? []);

        const ctxCme = document.getElementById('cmeComplaintsChart').getContext('2d');
        const cmeComplaintsChart = new Chart(ctxCme, {
            type: 'line',
            data: {
                labels: cmeLabels,
                datasets: [
                    {
                        label: 'Total Complaints',
                        data: cmeData,
                        borderColor: '#8b5cf6', // Violet
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#8b5cf6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Addressed Complaints',
                        data: cmeResolvedData,
                        borderColor: '#22c55e', // Green
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#22c55e',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 25
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 13,
                                weight: 'bold',
                                family: 'Arial, sans-serif'
                            },
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 12
                        },
                        mode: 'index',
                        intersect: false
                    },
                    datalabels: {
                        display: 'auto', // Automatically hide overlapping labels
                        clamp: true, // Keep labels within chart area
                        anchor: function(context) {
                            // Alternate anchor based on dataset index to reduce collision
                            return context.datasetIndex % 2 === 0 ? 'end' : 'start';
                        },
                        align: function(context) {
                            // Alternate alignment based on dataset index
                            return context.datasetIndex % 2 === 0 ? 'top' : 'bottom';
                        },
                        offset: 8,
                        font: {
                            size: 10,
                            weight: 'bold',
                            family: 'Arial, sans-serif'
                        },
                        color: function(context) {
                            return context.dataset.borderColor;
                        },
                        formatter: function(value) {
                            return value > 0 ? value : '';
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: 'bold',
                                family: 'Arial, sans-serif'
                            },
                            color: '#1f2937'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grace: '10%', // Add 10% extra space at the top
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            precision: 0
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });

        // Employee Performance Chart (Overlaid Bar)
        const empLabels = @json($empGraphLabels ?? []);
        const empTotalData = @json($empGraphTotal ?? []);
        const empResolvedData = @json($empGraphResolved ?? []);

        const ctxEmp = document.getElementById('employeePerformanceChart').getContext('2d');
        const employeePerformanceChart = new Chart(ctxEmp, {
            type: 'bar',
            data: {
                labels: empLabels,
                datasets: [
                    {
                        label: 'Total Complaints',
                        data: empTotalData,
                        backgroundColor: '#3b82f6', // Blue
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                        order: 2 // Draw first (behind)
                    },
                    {
                        label: 'Addressed Complaints',
                        data: empResolvedData,
                        backgroundColor: '#22c55e', // Green (Foreground)
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                        order: 1 // Draw second (on top)
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            font: {
                                size: 11
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 10
                        },
                        formatter: function(value) {
                            return value > 0 ? value : '';
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true, // Stacked true is needed for overlaying bars on same category index
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        stacked: false, // Y-axis NOT stacked, so they start from 0
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            precision: 0,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        // Complaints by Status Chart (Donut Chart) - Using same colors as admin side
        const statusMap = {
            'assigned': { label: 'Assigned', color: '#16a34a' }, // Green
            'in_progress': { label: 'In Progress', color: '#ec5454' }, // Red
            'resolved': { label: 'Addressed', color: '#64748b' }, // Grey
            'work_performa': { label: 'Work Performa', color: '#60a5fa' }, // Light Blue
            'maint_performa': { label: 'Maint Performa', color: '#eab308' }, // Yellow
            'work_priced_performa': { label: 'Work Priced', color: '#9333ea' }, // Purple
            'maint_priced_performa': { label: 'Maint Priced', color: '#ea580c' }, // Dark Orange
            'product_na': { label: 'Product N/A', color: '#0deb7c' }, // Green
            'un_authorized': { label: 'Un-Authorized', color: '#ec4899' }, // Pink
            'pertains_to_ge': { label: 'Pertains to GE', color: '#8b5cf6' }, // Violet
            'pertains_to_ge_const_isld': { label: 'Pertains to GE(N)', color: '#06b6d4' }, // Aqua/Cyan
            'barak_damages': { label: 'Barak Damages', color: '#808000' }, // Olive
            'closed': { label: 'Closed', color: '#6b7280' }, // Grey
            'new': { label: 'New', color: '#3b82f6' } // Blue
        };

        const statusKeys = Object.keys(complaintsByStatus);
        const statusLabels = statusKeys.map(key => {
            if (statusMap[key] && statusMap[key].label) {
                return statusMap[key].label;
            }
            // Fallback: format the key nicely
            return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        });
        const statusData = Object.values(complaintsByStatus);
        const statusColors = statusKeys.map(key => {
            if (statusMap[key] && statusMap[key].color) {
                return statusMap[key].color;
            }
            // Default color from admin side if status not found
            return '#64748b';
        });

        const ctx3 = document.getElementById('complaintsByStatusChart').getContext('2d');

        // Calculate total from original data (including closed) for accurate total count
        const totalComplaints = Object.values(complaintsByStatus).reduce((a, b) => a + b, 0);

        // Center text plugin for Chart.js
        const centerTextPlugin = {
            id: 'centerText',
            beforeDraw: function(chart) {
                const ctx = chart.ctx;
                const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
                const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2;

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                // Helper function for formatting numbers (1k, 1.5k, etc.)
                const formatNumber = (num) => {
                    if (num >= 1000) {
                        return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
                    }
                    return num;
                };

                // Get current chart data
                const currentLabels = chart.data.labels || [];
                const currentData = chart.data.datasets[0]?.data || [];
                const currentColors = chart.data.datasets[0]?.backgroundColor || [];
                const currentTotal = currentData.reduce((a, b) => a + b, 0);

                // Check if status filter is active
                const statusFilter = document.getElementById('filterStatus')?.value;
                const isStatusFiltered = statusFilter && statusFilter !== 'all';

                // Get hovered segment
                const activeElements = chart.getActiveElements();
                if (activeElements.length > 0) {
                    const activeIndex = activeElements[0].index;
                    const value = currentData[activeIndex];
                    const label = currentLabels[activeIndex];

                    // Show status name
                    ctx.font = 'bold 14px Arial';
                    ctx.fillStyle = '#1f2937';
                    ctx.fillText(label, centerX, centerY - 10);

                    // Show total complaints count only (no percentage)
                    ctx.font = 'bold 20px Arial';
                    ctx.fillStyle = currentColors[activeIndex] || '#3b82f6';
                    ctx.fillText(formatNumber(value), centerX, centerY + 15);

                    // Show label
                    ctx.font = '12px Arial';
                    ctx.fillStyle = '#6b7280';
                    ctx.fillText('Complaints', centerX, centerY + 35);
                } else if (isStatusFiltered) {
                    // Show filtered status when status filter is active
                    // Find the status in the chart data
                    let filteredIndex = -1;
                    let filteredValue = 0;
                    let filteredLabel = '';
                    let filteredColor = '#3b82f6';

                    // Get the expected label for the filtered status
                    const expectedLabel = statusMap[statusFilter]?.label;

                    // Find matching status in chart data
                    for (let i = 0; i < currentLabels.length; i++) {
                        const label = currentLabels[i];
                        // Match by label (case insensitive)
                        if (expectedLabel && label.toLowerCase() === expectedLabel.toLowerCase()) {
                            filteredIndex = i;
                            filteredValue = currentData[i];
                            filteredLabel = label;
                            filteredColor = currentColors[i] || statusMap[statusFilter]?.color || '#3b82f6';
                            break;
                        }
                    }

                    // If not found by label, try to find by status key in the original data
                    if (filteredIndex === -1 && complaintsByStatus && complaintsByStatus[statusFilter] !== undefined) {
                        // Find the index in the current chart data that corresponds to this status
                        const statusKeys = Object.keys(complaintsByStatus);
                        const statusIndex = statusKeys.indexOf(statusFilter);
                        if (statusIndex !== -1 && statusIndex < currentData.length) {
                            filteredIndex = statusIndex;
                            filteredValue = currentData[statusIndex];
                            filteredLabel = currentLabels[statusIndex] || expectedLabel || statusFilter;
                            filteredColor = currentColors[statusIndex] || statusMap[statusFilter]?.color || '#3b82f6';
                        }
                    }

                    if (filteredIndex !== -1 && filteredValue !== undefined) {
                        // Show filtered status name
                        ctx.font = 'bold 14px Arial';
                        ctx.fillStyle = '#1f2937';
                        ctx.fillText(filteredLabel, centerX, centerY - 10);

                        // Show filtered status count
                        ctx.font = 'bold 20px Arial';
                        ctx.fillStyle = filteredColor;
                        ctx.fillText(formatNumber(filteredValue), centerX, centerY + 15);

                        // Show label
                        ctx.font = '12px Arial';
                        ctx.fillStyle = '#6b7280';
                        ctx.fillText('Complaints', centerX, centerY + 35);
                    } else {
                        // Fallback to total if filtered status not found
                        ctx.font = 'bold 14px Arial';
                        ctx.fillStyle = '#1f2937';
                        ctx.fillText('Total', centerX, centerY - 10);

                        ctx.font = 'bold 20px Arial';
                        ctx.fillStyle = '#3b82f6';
                        ctx.fillText(formatNumber(currentTotal), centerX, centerY + 15);

                        ctx.font = '12px Arial';
                        ctx.fillStyle = '#6b7280';
                        ctx.fillText('Complaints', centerX, centerY + 35);
                    }
                } else {
                    // Show total when not hovering - smaller but bold and clear
                    ctx.font = 'bold 13px Arial';
                    ctx.fillStyle = '#1f2937';
                    ctx.fillText('Total', centerX, centerY - 12);

                    ctx.font = 'bold 18px Arial';
                    ctx.fillStyle = '#2563eb';
                    ctx.fillText(formatNumber(currentTotal), centerX, centerY + 8);

                    ctx.font = 'bold 11px Arial';
                    ctx.fillStyle = '#475569';
                    // ctx.fillText('Complaints', centerX, centerY + 28);
                }
                ctx.restore();
            }
        };

        const complaintsByStatusChart = new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Complaints',
                    data: statusData,
                    backgroundColor: statusColors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: {
                    animateRotate: true,
                    animateScale: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 12,
                                weight: 'bold',
                                family: 'Arial, sans-serif'
                            },
                            padding: 10,
                            usePointStyle: true,
                            color: '#1f2937'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        titleFont: {
                            size: 12
                        },
                        bodyFont: {
                            size: 11
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                const value = context.parsed;
                                const percentage = totalComplaints > 0 ? ((value / totalComplaints) * 100).toFixed(1) : 0;
                                if (label) {
                                    label += ': ';
                                }
                                label += value + ' (' + percentage + '%)';
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        display: false
                    }
                }
            },
            plugins: [centerTextPlugin]
        });

        // Add event listener for hover to update chart center text
        const chartCanvas = document.getElementById('complaintsByStatusChart');
        chartCanvas.addEventListener('mousemove', function() {
            complaintsByStatusChart.update('none');
        });
        chartCanvas.addEventListener('mouseleave', function() {
            complaintsByStatusChart.update('none');
        });

        // Filter functionality
        const filterSelects = document.querySelectorAll('.filter-select');
        const resetBtn = document.getElementById('resetFilters');

        // Handle filter changes
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                applyFilters();
            });
        });

        // Handle CMES change to update GE Groups and GE Nodes
        const filterCMES = document.getElementById('filterCMES');
        if (filterCMES) {
            filterCMES.addEventListener('change', function() {
                const cmesId = this.value;
                const category = document.getElementById('filterCategory').value;
                const status = document.getElementById('filterStatus').value;
                const dateRange = document.getElementById('filterDateRange').value;

                // Build params for reload (clear city/sector when CMES changes)
                const params = new URLSearchParams();
                if (cmesId) params.append('cmes_id', cmesId);
                if (category && category !== 'all') params.append('category', category);
                if (status && status !== 'all') params.append('status', status);
                if (dateRange) params.append('date_range', dateRange);

                // Reload page with new CMES filter to get updated GE Groups/Nodes
                window.location.href = '{{ route("frontend.dashboard") }}?' + params.toString();
            });
        }

        // Handle GE change to update GE Nodes
        document.getElementById('filterCity').addEventListener('change', function() {
            const cityId = this.value;
            const sectorSelect = document.getElementById('filterSector');
            const category = document.getElementById('filterCategory').value;
            const status = document.getElementById('filterStatus').value;
            const dateRange = document.getElementById('filterDateRange').value;
            const cmesId = document.getElementById('filterCMES') ? document.getElementById('filterCMES').value : null;

            // Clear GE Nodes selection when GE Group changes
            if (sectorSelect) {
                sectorSelect.value = '';
            }

            // Build params for reload
            const params = new URLSearchParams();
            if (cityId) params.append('city_id', cityId);
            // Don't include sector_id when city changes
            if (cmesId) params.append('cmes_id', cmesId);
            if (category && category !== 'all') params.append('category', category);
            if (status && status !== 'all') params.append('status', status);
            if (dateRange) params.append('date_range', dateRange);

            // Reload page with new city filter to get updated GE Nodes dropdown
            window.location.href = '{{ route("frontend.dashboard") }}?' + params.toString();
        });



        // Reset filters
        resetBtn.addEventListener('click', function() {
            window.location.href = '{{ route("frontend.dashboard") }}';
        });

        function applyFilters() {
            const cityId = document.getElementById('filterCity') ? document.getElementById('filterCity').value : null;
            const sectorId = document.getElementById('filterSector') ? document.getElementById('filterSector').value : null;
            const category = document.getElementById('filterCategory') ? document.getElementById('filterCategory').value : null;
            const status = document.getElementById('filterStatus') ? document.getElementById('filterStatus').value : null;
            const dateRange = document.getElementById('filterDateRange') ? document.getElementById('filterDateRange').value : null;
            const cmesId = document.getElementById('filterCMES') ? document.getElementById('filterCMES').value : null;

            const params = new URLSearchParams();
            if (cmesId) params.append('cmes_id', cmesId);
            if (cityId) params.append('city_id', cityId);
            if (sectorId) params.append('sector_id', sectorId);
            if (category && category !== 'all') params.append('category', category);
            if (status && status !== 'all') params.append('status', status);
            if (dateRange) params.append('date_range', dateRange);

            // Show loading state
            const statBoxes = document.querySelectorAll('[id^="stat-"]');
            statBoxes.forEach(box => {
                box.textContent = '...';
            });

            // Fetch data via AJAX
            fetch('{{ route("frontend.dashboard") }}?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                // Check if response is JSON
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    return response.json();
                } else {
                    // If not JSON, stop and don't reload
                    console.warn('Response is not JSON, stopping filter update');
                    return null;
                }
            })
            .then(data => {
                if (data && data.stats) {
                    // Update stats boxes
                    updateStats(data.stats);

                    // Update charts
                    updateCharts(data);

                    // Update URL without reload
                    window.history.pushState({}, '', '{{ route("frontend.dashboard") }}?' + params.toString());
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                // Don't auto-reload on error, just log it
            });
        }

        function updateStats(stats) {
            // Update all stat boxes
            if (stats.total_complaints !== undefined) {
                document.getElementById('stat-total-complaints').textContent = stats.total_complaints || 0;
            }
            if (stats.in_progress !== undefined) {
                document.getElementById('stat-in-progress').textContent = stats.in_progress || 0;
            }
            if (stats.addressed !== undefined) {
                document.getElementById('stat-addressed').textContent = stats.addressed || 0;
            }
            if (stats.work_performa !== undefined) {
                document.getElementById('stat-work-performa').textContent = stats.work_performa || 0;
            }
            if (stats.maint_performa !== undefined) {
                document.getElementById('stat-maint-performa').textContent = stats.maint_performa || 0;
            }
            if (stats.un_authorized !== undefined) {
                document.getElementById('stat-un-authorized').textContent = stats.un_authorized || 0;
            }
            if (stats.product !== undefined) {
                document.getElementById('stat-product').textContent = stats.product || 0;
            }
            if (stats.resolution_rate !== undefined) {
                document.getElementById('stat-resolution-rate').textContent = (stats.resolution_rate || 0) + '%';
            }
            if (stats.pertains_to_ge_const_isld !== undefined) {
                document.getElementById('stat-pertains-ge').textContent = stats.pertains_to_ge_const_isld || 0;
            }
            if (stats.assigned !== undefined) {
                document.getElementById('stat-assigned').textContent = stats.assigned || 0;
            }
        }

        function updateCharts(data) {
            // Update Monthly Complaints Chart
            if (data.monthlyComplaints && monthlyComplaintsChart) {
                monthlyComplaintsChart.data.datasets[0].data = data.monthlyComplaints;
                monthlyComplaintsChart.data.labels = data.monthLabels;
                monthlyComplaintsChart.update();
            }

            // Update Complaints by Status Chart
            if (data.complaintsByStatus && complaintsByStatusChart) {
                // Update global complaintsByStatus with all data (including closed)
                complaintsByStatus = data.complaintsByStatus;

                const statusKeys = Object.keys(data.complaintsByStatus);
                const statusLabels = statusKeys.map(key => {
                    if (statusMap[key] && statusMap[key].label) {
                        return statusMap[key].label;
                    }
                    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                });
                const statusData = Object.values(data.complaintsByStatus);
                const statusColors = statusKeys.map(key => {
                    if (statusMap[key] && statusMap[key].color) {
                        return statusMap[key].color;
                    }
                    return '#64748b';
                });

                complaintsByStatusChart.data.labels = statusLabels;
                complaintsByStatusChart.data.datasets[0].data = statusData;
                complaintsByStatusChart.data.datasets[0].backgroundColor = statusColors;
                complaintsByStatusChart.update('none');
            }

            // Update Resolution Trend Chart
            if (data.recentEdData && data.resolvedVsEdData && resolutionTrendChart) {
                resolutionTrendChart.data.datasets[0].data = data.recentEdData;
                resolutionTrendChart.data.datasets[1].data = data.resolvedVsEdData;
                resolutionTrendChart.data.labels = data.monthLabels;
                resolutionTrendChart.data.labels = data.monthLabels;
                resolutionTrendChart.update();
            }

            // Update CME Complaints Chart
            if (data.cmeGraphData && cmeComplaintsChart) {
                cmeComplaintsChart.data.datasets[0].data = data.cmeGraphData;
                if (data.cmeResolvedData) {
                    cmeComplaintsChart.data.datasets[1].data = data.cmeResolvedData;
                }
                if (data.cmeGraphLabels) {
                    cmeComplaintsChart.data.labels = data.cmeGraphLabels;
                }
                cmeComplaintsChart.update();
            }

            // Update Category Usage Chart
            if (data.categoryUsageValues && categoryUsageChart) {
                categoryUsageChart.data.datasets[0].data = data.categoryTotalReceivedValues;
                categoryUsageChart.data.datasets[1].data = data.categoryUsageValues;
                if (data.categoryLabels) {
                    categoryUsageChart.data.labels = data.categoryLabels;
                }
                categoryUsageChart.update();
            }
        }

        // Handle CME Graph Filter Change
        const cmeGraphFilter = document.getElementById('cmeGraphFilter');
        if (cmeGraphFilter) {
            cmeGraphFilter.addEventListener('change', function() {
                const cmeDateRange = this.value;

                // Get other current filters to maintain context
                const cityId = document.getElementById('filterCity') ? document.getElementById('filterCity').value : null;
                const sectorId = document.getElementById('filterSector') ? document.getElementById('filterSector').value : null;
                const category = document.getElementById('filterCategory') ? document.getElementById('filterCategory').value : null;
                const status = document.getElementById('filterStatus') ? document.getElementById('filterStatus').value : null;
                const dateRange = document.getElementById('filterDateRange') ? document.getElementById('filterDateRange').value : null;
                const cmesId = document.getElementById('filterCMES') ? document.getElementById('filterCMES').value : null;

                const params = new URLSearchParams();
                if (cmeDateRange) params.append('cme_date_range', cmeDateRange);
                if (cmesId) params.append('cmes_id', cmesId);
                if (cityId) params.append('city_id', cityId);
                if (sectorId) params.append('sector_id', sectorId);
                if (category && category !== 'all') params.append('category', category);
                if (status && status !== 'all') params.append('status', status);
                if (dateRange) params.append('date_range', dateRange);

                // Fetch data via AJAX
                fetch('{{ route("frontend.dashboard") }}?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        // Update ONLY the CME chart
                        if (data.cmeGraphData && cmeComplaintsChart) {
                            cmeComplaintsChart.data.datasets[0].data = data.cmeGraphData;
                            if (data.cmeResolvedData) {
                                cmeComplaintsChart.data.datasets[1].data = data.cmeResolvedData;
                            }
                            if (data.cmeGraphLabels) {
                                cmeComplaintsChart.data.labels = data.cmeGraphLabels;
                            }
                            cmeComplaintsChart.update();
                        }
                    }
                })
                .catch(error => console.error('Error updating CME graph:', error));
            });
        }
    });

    function exportTableToExcel(tableID, filename = ''){
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML;

        filename = filename?filename+'.xls':'excel_data.xls';

        downloadLink = document.createElement("a");

        document.body.appendChild(downloadLink);

        if(navigator.msSaveOrOpenBlob){
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob( blob, filename);
        }else{
            downloadLink.href = 'data:' + dataType + ', ' + encodeURIComponent(tableHTML);
            downloadLink.download = filename;
            downloadLink.click();
        }
    }

    function printSection(divId) {
        // Remove printable-area class from any existing element
        document.querySelectorAll('.printable-area').forEach(el => el.classList.remove('printable-area'));

        // Add printable-area class to the target element
        const target = document.getElementById(divId);
        if (target) {
            target.classList.add('printable-area');
            window.print();
        }
    }

    // Category Usage Bar Chart
    const categoryUsageCtx = document.getElementById('categoryUsageChart');
    if (categoryUsageCtx) {
        const categoryLabels = @json($categoryLabels ?? []);
        const categoryUsageValues = @json($categoryUsageValues ?? []);
        const categoryTotalReceivedValues = @json($categoryTotalReceivedValues ?? []);

        const categoryUsageChart = new Chart(categoryUsageCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [
                    {
                        label: 'Total Stock',
                        data: categoryTotalReceivedValues,
                        hidden: true, // Hide from chart and scale
                        backgroundColor: '#3b82f6', // Blue (for Tooltip)
                    },
                    {
                        label: 'Used Quantity',
                        data: categoryUsageValues,
                        backgroundColor: '#22c55e', // Green (Foreground)
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                        maxBarThickness: 40, // Limit bar width
                        order: 1 // Draw second (on top)
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            font: {
                                size: 11
                            },
                            filter: function(item, chart) {
                                // Hide Total Stock from legend
                                return item.text !== 'Total Stock';
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 10
                        },
                        formatter: function(value) {
                            return value > 0 ? value : '';
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        titleFont: {
                            size: 12
                        },
                        bodyFont: {
                            size: 11
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y;
                                return label;
                            },
                            afterBody: function(context) {
                                // Manually show Total Stock since it's hidden
                                if (context[0].dataset.label === 'Used Quantity') {
                                    const index = context[0].dataIndex;
                                    const totalStock = context[0].chart.data.datasets[0].data[index];
                                    return 'Total Stock: ' + totalStock;
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            precision: 0,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        // Handle Category Graph Filter Change (AJAX) - Scoped to this block to access categoryUsageChart
        const categoryGraphFilter = document.getElementById('categoryGraphFilter');
        if (categoryGraphFilter) {
            categoryGraphFilter.addEventListener('change', function() {
                const dateRange = this.value;
                const cityId = document.getElementById('filterCity') ? document.getElementById('filterCity').value : null;
                const sectorId = document.getElementById('filterSector') ? document.getElementById('filterSector').value : null;
                const cmesId = document.getElementById('filterCMES') ? document.getElementById('filterCMES').value : null;

                // Keep existing global filters context
                const category = document.getElementById('filterCategory') ? document.getElementById('filterCategory').value : null;
                const status = document.getElementById('filterStatus') ? document.getElementById('filterStatus').value : null;

                const params = new URLSearchParams();
                if (dateRange) params.append('category_date_range', dateRange);
                if (cmesId) params.append('cmes_id', cmesId);
                if (cityId) params.append('city_id', cityId);
                if (sectorId) params.append('sector_id', sectorId);
                if (category && category !== 'all') params.append('category', category);
                if (status && status !== 'all') params.append('status', status);

                // Fetch data via AJAX
                fetch('{{ route("frontend.dashboard") }}?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.categoryLabels) {
                        // Update Category Usage Chart
                        categoryUsageChart.data.labels = data.categoryLabels;
                        categoryUsageChart.data.datasets[0].data = data.categoryTotalReceivedValues; // Total Stock (Hidden)
                        categoryUsageChart.data.datasets[1].data = data.categoryUsageValues; // Used Quantity
                        categoryUsageChart.update();
                    }
                })
                .catch(error => console.error('Error updating category chart:', error));
            });
        }
    }


    </script>
    <style>
    @media print {
        @page {
            size: landscape;
            margin: 5mm;
        }
        body * {
            visibility: hidden;
        }
        .printable-area, .printable-area * {
            visibility: visible;
        }
        .printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            background: white;
        }
        /* Optimize table for print */
        table {
            width: 100%;
            font-size: 10px; /* Smaller font */
            border-collapse: collapse;
        }
        th, td {
            padding: 4px 2px !important; /* Reduce padding */
            border: 1px solid #ddd !important; /* Ensure borders are visible */
            white-space: nowrap; /* Prevent wrapping if possible */
        }
        /* Hide scrollbars */
        .overflow-x-auto {
            overflow: visible !important;
        }
        .no-print {
            display: none !important;
        }
        /* Show print-only elements */
        .print-only {
            display: block !important;
            padding: 10px;
        }
    }
    </style>
@endpush
