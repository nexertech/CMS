<table id="stockConsumptionTable" class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-2 py-1 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200 sticky left-0 bg-gray-50 z-10">Item Name</th>
            @foreach($monthLabels as $month)
                <th class="px-4 py-1 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200">
                    {{ substr($month, 0, 3) }}
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
            $stockMonthTotalsReceivedTop10 = array_fill_keys($monthLabels, 0);
            $grandTotalReceived = 0;
            $top10TotalReceived = 0;
            $grandTotalUsed = 0;
            $top10TotalUsed = 0;
            $grandBalance = 0;
            $top10Balance = 0;
            $rowIndex = 0;
        @endphp
        @foreach($stockConsumptionData as $itemName => $data)
            @php
                $rowIndex++;
                $grandTotalReceived += $data['total_received'];
                $grandTotalUsed += $data['total_used'];
                $grandBalance += $data['current_stock'];
                if ($rowIndex <= 10) {
                    $top10TotalReceived += $data['total_received'];
                    $top10TotalUsed += $data['total_used'];
                    $top10Balance += $data['current_stock'];
                }
                $rowClass = $rowIndex > 10 ? 'no-print-row' : '';
            @endphp
            <tr class="hover:bg-blue-50 {{ $rowClass }}">
                <td class="px-2 py-1 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200 sticky left-0 bg-white z-10">{{ $itemName }}</td>
                @foreach($monthLabels as $month)
                    @php
                        $receivedQty = $data['monthly_received_data'][$month] ?? 0;
                        $stockMonthTotalsReceived[$month] += $receivedQty;
                        if ($rowIndex <= 10) {
                            $stockMonthTotalsReceivedTop10[$month] += $receivedQty;
                        }
                    @endphp
                    <td class="px-4 py-1 whitespace-nowrap text-sm text-center text-blue-600 font-semibold border-r border-gray-200" style="background-color: #eff6ff;">
                        {{ $receivedQty > 0 ? $receivedQty : '-' }}
                    </td>
                @endforeach
                <td class="px-4 py-1 whitespace-nowrap text-sm text-center font-bold text-gray-700 border-r border-gray-200">{{ $data['total_received'] }}</td>
                <td class="px-4 py-1 whitespace-nowrap text-sm text-center font-bold text-red-600 border-r border-gray-200">{{ $data['total_used'] }}</td>
                <td class="px-4 py-1 whitespace-nowrap text-sm text-center font-bold text-green-600">{{ $data['current_stock'] }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot class="bg-gray-100 font-bold">
        <tr class="border-t-2 border-gray-400 no-print">
            <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200 sticky left-0 bg-gray-100 z-10">Total (Top 10)</td>
            @foreach($monthLabels as $month)
                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-blue-600 border-r border-gray-200">{{ $stockMonthTotalsReceivedTop10[$month] }}</td>
            @endforeach
            <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border-r border-gray-200">{{ $top10TotalReceived }}</td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-red-600 border-r border-gray-200">{{ $top10TotalUsed }}</td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-green-600">{{ $top10Balance }}</td>
        </tr>
        <tr class="border-t-2 border-gray-400 no-print-row">
            <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200 sticky left-0 bg-gray-100 z-10">Grand Total</td>
            @foreach($monthLabels as $month)
                <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-blue-600 border-r border-gray-200">{{ $stockMonthTotalsReceived[$month] }}</td>
            @endforeach
            <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border-r border-gray-200">{{ $grandTotalReceived }}</td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-red-600 border-r border-gray-200">{{ $grandTotalUsed }}</td>
            <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-green-600">{{ $grandBalance }}</td>
        </tr>
    </tfoot>
</table>
