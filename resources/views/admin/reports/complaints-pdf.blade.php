<!DOCTYPE html>
<html>
<head>
    <title>SUB-DIVISION WISE PERFORMANCE Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 5px 0;
        }
        .header p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            font-size: 11px;
        }
        th {
            background-color: #333;
            color: #fff;
            font-weight: bold;
        }
        td {
            background-color: #fff;
            color: #000;
        }
        .fw-bold {
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>CMS COMPLAINT MANAGEMENT SYSTEM</h2>
        <p><strong>SUB-DIVISION WISE PERFORMANCE</strong></p>
        <p>From: {{ $dateFrom }} To: {{ $dateTo }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="text-center" style="width: 200px;">Description</th>
                @foreach($categories as $catKey => $catName)
                  <th colspan="2" class="text-center">{{ $catName }}</th>
                @endforeach
                <th colspan="2" class="text-center">Total</th>
            </tr>
            <tr>
                @foreach($categories as $catKey => $catName)
                  <th class="text-center">Qty (No's)</th>
                  <th class="text-center">%age</th>
                @endforeach
                <th class="text-center">Qty (No's)</th>
                <th class="text-center">%age</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $rowKey => $row)
            <tr>
                <td class="fw-bold">{{ $row['name'] }}</td>
                @foreach($categories as $catKey => $catName)
                    @php
                        $cellData = $row['categories'][$catKey] ?? ['count' => 0, 'percentage' => 0];
                    @endphp
                    <td class="text-center">{{ number_format($cellData['count']) }}</td>
                    <td class="text-center">{{ number_format($cellData['percentage'], 1) }}%</td>
                @endforeach
                @php
                    // Calculate grand total: sum of all primary columns
                    // Individual E&M NRC columns (Electric, Gas, Water Supply) should be EXCLUDED from Total
                    // E&M NRC (Total) should be INCLUDED in Total
                    $rowGrandTotal = 0;
                    $emNrcTotalKeyLocal = $emNrcTotalKey ?? 'em_nrc_total';
                    $hasEmNrcTotal = isset($row['categories'][$emNrcTotalKeyLocal]);
                    
                    foreach ($row['categories'] as $catKey => $catData) {
                      // Always include E&M NRC Total if it exists
                      if ($catKey === $emNrcTotalKeyLocal) {
                        $rowGrandTotal += $catData['count'] ?? 0;
                      } 
                      // For other categories, check if it's an individual E&M NRC column
                      elseif (isset($categories[$catKey])) {
                        $catName = $categories[$catKey];
                        
                        // Skip individual E&M NRC columns (Electric, Gas, Water Supply) if E&M NRC Total exists
                        $isIndividualEmNrc = false;
                        if ($hasEmNrcTotal) {
                          // Check if this is one of the 3 individual E&M NRC columns
                          if (stripos($catName, 'E&M NRC') !== false && stripos($catName, 'Total') === false) {
                            $isIndividualEmNrc = true;
                          }
                        }
                        
                        // Include all other columns (non-individual E&M NRC columns)
                        if (!$isIndividualEmNrc) {
                          $rowGrandTotal += $catData['count'] ?? 0;
                        }
                      } else {
                        // Include if category key not found in categories array (fallback)
                        $rowGrandTotal += $catData['count'] ?? 0;
                      }
                    }
                    $rowGrandPercent = $grandTotal > 0 ? ($rowGrandTotal / $grandTotal * 100) : 0;
                @endphp
                <td class="text-center fw-bold">{{ number_format($rowGrandTotal) }}</td>
                <td class="text-center fw-bold">{{ number_format($rowGrandPercent, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

