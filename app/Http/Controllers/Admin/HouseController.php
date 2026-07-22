<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\City;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\LocationFilterTrait;
use Exception;

class HouseController extends Controller
{
    use LocationFilterTrait;
    /**
     * Display a listing of the houses.
     */
    public function index(Request $request)
    {
        $query = House::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('house_no', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by city (GE Group)
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by sector (GE Node)
        if ($request->has('sector_id') && $request->sector_id) {
            $query->where('sector_id', $request->sector_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $user = Auth::user();
        $this->filterHousesByLocation($query, $user);

        $houses = $query->with(['city', 'sector'])->orderBy('id', 'desc')->paginate(10);
        
        // Get cities and sectors for filter dropdowns based on user permissions
        $cityIds = $this->getUserCityIds($user);
        $sectorIds = $this->getUserSectorIds($user);

        $citiesQuery = City::where('status', 1);
        if ($cityIds !== null) {
            $citiesQuery->whereIn('id', $cityIds);
        }
        $cities = $citiesQuery->orderBy('name')->get();

        $sectorsQuery = Sector::where('status', 1);
        if ($sectorIds !== null) {
            $sectorsQuery->whereIn('id', $sectorIds);
        }
        $sectors = $sectorsQuery->orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.houses.index', compact('houses', 'cities', 'sectors'))->renderSections()['content'],
                'total' => $houses->total(),
                'pagination' => $houses->links()->toHtml()
            ]);
        }
        
        return view('admin.houses.index', compact('houses', 'cities', 'sectors'));
    }

    /**
     * Show the form for creating a new house.
     */
    public function create()
    {
        $user = Auth::user();
        $cityIds = $this->getUserCityIds($user);

        $citiesQuery = City::where('status', 1);
        if ($cityIds !== null) {
            $citiesQuery->whereIn('id', $cityIds);
        }
        $cities = $citiesQuery->orderBy('name')->get();
        
        $defaultCityId = !empty($user->city_ids) ? $user->city_ids[0] : null;
        $defaultSectorId = !empty($user->sector_ids) ? $user->sector_ids[0] : null;
        
        return view('admin.houses.create', compact('cities', 'defaultCityId', 'defaultSectorId'));
    }

    /**
     * Store a newly created house in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|max:150|unique:houses,username',
            'house_no' => 'required|string|max:150',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|regex:/^[0-9]{11}$/',
            'password' => 'nullable|string|min:8',
            'city_id' => 'required|exists:cities,id',
            'sector_id' => 'required|exists:sectors,id',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|in:0,1',
            'type' => 'nullable|string|max:100',
        ], [
            'phone.regex' => 'Phone number must be exactly 11 digits (e.g. 03001234567).',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $house = House::create([
                'username' => $request->username,
                'house_no' => $request->house_no,
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => $request->password, // Will be hashed by mutator
                'city_id' => $request->city_id,
                'sector_id' => $request->sector_id,
                'address' => $request->address,
                'status' => $request->status ?? 1,
                'type' => $request->type,
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'House created successfully.',
                    'house' => $house
                ]);
            }

            return redirect()->route('admin.houses.index')
                ->with('success', 'House created successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating house: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating house: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error creating house: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified house.
     */
    public function show(House $house)
    {
        $house->load(['city', 'sector']);
        
        if (request()->ajax() && request()->header('Accept') === 'text/html') {
            return view('admin.houses.show', compact('house'));
        }
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'house' => $house
            ]);
        }
        
        return view('admin.houses.show', compact('house'));
    }

    /**
     * Show the form for editing the specified house.
     */
    public function edit(House $house)
    {
        $user = Auth::user();
        $cityIds = $this->getUserCityIds($user);
        $sectorIds = $this->getUserSectorIds($user);

        // Filter cities
        $citiesQuery = City::where('status', 1);
        if ($cityIds !== null) {
            $citiesQuery->whereIn('id', $cityIds);
        }
        $cities = $citiesQuery->orderBy('name')->get();

        // Filter sectors
        $sectorsQuery = Sector::where('status', 1)->where('city_id', $house->city_id);
        if ($sectorIds !== null) {
            $sectorsQuery->whereIn('id', $sectorIds);
        }
        $sectors = $sectorsQuery->orderBy('name')->get();
        
        return view('admin.houses.edit', compact('house', 'cities', 'sectors'));
    }

    /**
     * Update the specified house in storage.
     */
    public function update(Request $request, House $house)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['nullable', 'string', 'max:150', Rule::unique('houses')->ignore($house->id)],
            'house_no' => 'required|string|max:150',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|regex:/^[0-9]{11}$/',
            'password' => 'nullable|string|min:8',
            'city_id' => 'required|exists:cities,id',
            'sector_id' => 'required|exists:sectors,id',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:0,1',
            'type' => 'nullable|string|max:100',
        ], [
            'phone.regex' => 'Phone number must be exactly 11 digits (e.g. 03001234567).',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'username' => $request->username,
                'house_no' => $request->house_no,
                'name' => $request->name,
                'phone' => $request->phone,
                'city_id' => $request->city_id,
                'sector_id' => $request->sector_id,
                'address' => $request->address,
                'status' => $request->status,
                'type' => $request->type,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = $request->password; // Will be hashed by mutator
            }

            $house->update($updateData);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'House updated successfully.',
                    'house' => $house
                ]);
            }

            return redirect()->route('admin.houses.index')
                ->with('success', 'House updated successfully.');
                
        } catch (Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating house: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error updating house: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified house from storage.
     */
    public function destroy($id)
    {
        try {
            $house = House::findOrFail($id);
            
            Log::info('Attempting to soft delete house ID: ' . $house->id);
            
            $house->delete(); // Soft delete
            Log::info('House soft deleted successfully for ID: ' . $house->id);

            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'House deleted successfully.'
                ]);
            }

            return redirect()->route('admin.houses.index')
                ->with('success', 'House deleted successfully.');

        } catch (Exception $e) {
            Log::error('Error deleting house ID ' . ($house->id ?? $id) . ': ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting house: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting house: ' . $e->getMessage());
        }
    }

    /**
     * Get sectors by city (AJAX)
     */
    public function getSectorsByCity(Request $request)
    {
        $user = Auth::user();
        $cityId = $request->input('city_id');
        
        if (!$cityId || $cityId <= 0) {
            return response()->json(['sectors' => []]);
        }

        $query = Sector::where('city_id', '=', $cityId)
            ->where('status', '=', 1);

        // Apply data isolation
        $sectorIds = $this->getUserSectorIds($user);
        if ($sectorIds !== null) {
            $query->whereIn('id', $sectorIds);
        }

        $sectors = $query->orderBy('name', 'asc')->get(['id', 'name']);
        
        return response()->json(['sectors' => $sectors]);
    }

    /**
     * Download sample CSV for house import
     */
    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="house_import_sample.csv"',
        ];

        $columns = ['House No', 'Resident Name', 'Username', 'Phone', 'GE Group', 'GE Node', 'Type', 'Address', 'Status'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            $sampleCity = City::first()?->name ?? 'Isld Maint';
            $sampleSector = Sector::first()?->name ?? 'Sector A';

            fputcsv($file, [
                'H-101',
                'Ali Khan',
                'h101_ali',
                '03001234567',
                $sampleCity,
                $sampleSector,
                'Resident',
                'House 101, Street 5',
                'Active'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import houses from CSV/Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xls,xlsx|max:10240',
        ], [
            'file.required' => 'Please select a CSV or Excel file to import.',
            'file.mimes' => 'The file must be a CSV or Excel format (.csv, .xls, .xlsx).',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($path)) {
                $rows = $xlsx->rows();
            } else {
                return redirect()->back()->with('error', 'Unable to parse Excel file: ' . \Shuchkin\SimpleXLSX::parseError());
            }
        } else {
            // Read CSV file
            $handle = fopen($path, 'r');
            if (!$handle) {
                return redirect()->back()->with('error', 'Unable to open file.');
            }

            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $delimiter = ',';
            $firstLine = fgetcsv($handle, 2000, ',');
            if (!$firstLine || count($firstLine) < 2) {
                rewind($handle);
                if ($bom === "\xEF\xBB\xBF") {
                    fread($handle, 3);
                }
                $firstLine = fgetcsv($handle, 2000, ';');
                $delimiter = ';';
            }

            if ($firstLine) {
                $rows[] = $firstLine;
                while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
                    $rows[] = $data;
                }
            }
            fclose($handle);
        }

        if (empty($rows) || count($rows) < 2) {
            return redirect()->back()->with('error', 'File is empty or missing data rows.');
        }

        $header = array_shift($rows); // First row is header
        $headerMap = [];
        foreach ($header as $index => $colName) {
            $cleanCol = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace([' ', '-'], '_', (string)$colName))));
            if ($cleanCol !== '') {
                $headerMap[$cleanCol] = $index;
            }
        }

        $cities = City::all()->keyBy(fn($item) => strtolower(trim($item->name)));
        $citiesById = City::all()->keyBy('id');
        $sectors = Sector::all()->keyBy(fn($item) => strtolower(trim($item->name)));
        $sectorsById = Sector::all()->keyBy('id');

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                if (!is_array($row) || array_filter($row, fn($val) => trim((string)$val) !== '') === []) {
                    continue;
                }

                $getValue = function($key) use ($headerMap, $row) {
                    if (isset($headerMap[$key]) && isset($row[$headerMap[$key]])) {
                        return trim((string)$row[$headerMap[$key]]);
                    }
                    return null;
                };

                $houseNo = $getValue('house_no') ?? $getValue('houseno') ?? ($row[0] ?? null);
                $name = $getValue('resident_name') ?? $getValue('name') ?? ($row[1] ?? null);
                $username = $getValue('username') ?? ($row[2] ?? null);
                $phone = $getValue('phone') ?? ($row[3] ?? null);
                $cityVal = $getValue('ge_group') ?? $getValue('city') ?? $getValue('city_id') ?? ($row[4] ?? null);
                $sectorVal = $getValue('ge_node') ?? $getValue('sector') ?? $getValue('sector_id') ?? ($row[5] ?? null);
                $type = $getValue('type') ?? ($row[6] ?? null);
                $address = $getValue('address') ?? ($row[7] ?? null);
                $statusVal = $getValue('status') ?? ($row[8] ?? '1');

                if ($houseNo === null) {
                    $skipped++;
                    continue;
                }

                $houseNo = trim((string)$houseNo);

                // Clean string and filter out non-printable ASCII/junk
                if (preg_match('/[^\x20-\x7E\t\r\n]/', $houseNo)) {
                    $houseNo = preg_replace('/[^\x20-\x7E]/', '', $houseNo);
                }

                if (empty($houseNo)) {
                    $skipped++;
                    continue;
                }

                // City lookup
                $cityId = null;
                if (!empty($cityVal)) {
                    if (is_numeric($cityVal) && isset($citiesById[$cityVal])) {
                        $cityId = (int)$cityVal;
                    } else {
                        $cityKey = strtolower(trim((string)$cityVal));
                        $cityId = $cities[$cityKey]->id ?? null;
                    }
                }
                if (!$cityId && $cities->count() > 0) {
                    $cityId = $cities->first()->id;
                }

                // Sector lookup
                $sectorId = null;
                if (!empty($sectorVal)) {
                    if (is_numeric($sectorVal) && isset($sectorsById[$sectorVal])) {
                        $sectorId = (int)$sectorVal;
                    } else {
                        $sectorKey = strtolower(trim((string)$sectorVal));
                        $sectorId = $sectors[$sectorKey]->id ?? null;
                    }
                }
                if (!$sectorId && $sectors->count() > 0) {
                    $sectorId = $sectors->first()->id;
                }

                // Phone
                if (!empty($phone)) {
                    $phone = preg_replace('/[^0-9]/', '', (string)$phone);
                    if (strlen($phone) === 10 && str_starts_with($phone, '3')) {
                        $phone = '0' . $phone;
                    } elseif (strlen($phone) === 12 && str_starts_with($phone, '92')) {
                        $phone = '0' . substr($phone, 2);
                    }
                }

                // Status
                $status = 1;
                if ($statusVal !== null && in_array(strtolower(trim((string)$statusVal)), ['0', 'inactive', 'false', 'disabled'], true)) {
                    $status = 0;
                }

                House::create([
                    'house_no' => $houseNo,
                    'name' => $name ? trim((string)$name) : null,
                    'username' => $username ? trim((string)$username) : null,
                    'phone' => $phone ?: null,
                    'city_id' => $cityId,
                    'sector_id' => $sectorId,
                    'address' => $address ? trim((string)$address) : null,
                    'type' => $type ? trim((string)$type) : null,
                    'status' => $status,
                ]);

                $imported++;
            }

            DB::commit();

            $msg = "Successfully imported {$imported} house(s).";
            if ($skipped > 0) {
                $msg .= " Skipped {$skipped} empty/invalid row(s).";
            }

            return redirect()->route('admin.houses.index')->with('success', $msg);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("House import failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
