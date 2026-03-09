<table id="monthlyPerformanceTable" class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th rowspan="2"
                class="px-4 py-1 text-left text-xs font-extrabold text-gray-900 uppercase tracking-wider border-r border-gray-200 sticky left-0 bg-gray-50 z-10">
                @php $firstKey = array_key_first($monthlyTableData ?? []); @endphp
                {{ is_numeric($firstKey) ? 'Year' : 'Month' }}</th>
            @foreach($tableEntities as $entity)
                <th colspan="2"
                    class="px-2 py-1 text-center text-xs font-extrabold text-gray-900 uppercase tracking-wider border-r border-gray-200">
                    {{ $entity->name }}
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach($tableEntities as $entity)
                <th class="px-2 py-1 text-center text-xs font-bold text-gray-900 uppercase tracking-wider border-r border-gray-200">Total</th>
                <th class="px-2 py-1 text-center text-xs font-bold text-gray-900 uppercase tracking-wider border-r border-gray-200">Addressed</th>
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
                <td class="px-4 py-1 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200 sticky left-0 bg-white z-10">
                    {{ $month }}
                </td>
                @foreach($tableEntities as $entity)
                    @php
                        $t = $data[$entity->name]['total'] ?? 0;
                        $r = $data[$entity->name]['resolved'] ?? 0;
                        $columnTotals[$entity->name]['total'] += $t;
                        $columnTotals[$entity->name]['resolved'] += $r;
                    @endphp
                    <td class="px-2 py-1 whitespace-nowrap text-sm text-center text-gray-900 border-r border-gray-200">{{ $t }}</td>
                    <td class="px-2 py-1 whitespace-nowrap text-sm text-center text-green-600 font-medium border-r border-gray-200">{{ $r }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
    <tfoot class="bg-gray-100 font-bold">
        <tr>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200 sticky left-0 bg-gray-100 z-10">Total</td>
            @foreach($tableEntities as $entity)
                <td class="px-2 py-3 whitespace-nowrap text-sm text-center text-gray-900 border-r border-gray-200">{{ $columnTotals[$entity->name]['total'] }}</td>
                <td class="px-2 py-3 whitespace-nowrap text-sm text-center text-green-600 border-r border-gray-200">{{ $columnTotals[$entity->name]['resolved'] }}</td>
            @endforeach
        </tr>
    </tfoot>
</table>
