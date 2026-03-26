<?php

namespace App\Http\Controllers;

use App\ImportLog;
use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('manage_settings')) {
            return redirect()->route('dashboard')->with('error', 'Permission denied.');
        }

        $logs = ImportLog::with('creator')->orderBy('created_at', 'desc')->paginate(20);
        return view('imports.index', compact('logs'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('manage_settings')) {
            return redirect()->route('dashboard')->with('error', 'Permission denied.');
        }

        return view('imports.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('manage_settings')) {
            return redirect()->route('dashboard')->with('error', 'Permission denied.');
        }

        $request->validate([
            'import_type' => 'required|in:customers,exchange_rates',
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $path = $file->getRealPath();

        $data = array_map('str_getcsv', file($path));
        $header = array_shift($data);

        $total = count($data);
        $success = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                if (count($header) !== count($row)) {
                    $failed++;
                    $errors[] = "Row " . ($index + 1) . ": Column count mismatch.";
                    continue;
                }

                $rowData = array_combine($header, $row);

                if ($request->import_type === 'customers') {
                    if (empty($rowData['name']) || empty($rowData['id_number'])) {
                        $failed++;
                        $errors[] = "Row " . ($index + 1) . ": Missing name or id_number.";
                        continue;
                    }

                    Customer::updateOrCreate(
                        ['id_number' => $rowData['id_number']],
                        [
                            'name' => $rowData['name'],
                            'phone' => $rowData['phone'] ?? null,
                            'address' => $rowData['address'] ?? null,
                            'type' => $rowData['type'] ?? 'individual',
                            'email' => $rowData['email'] ?? null,
                            'created_by' => Auth::id()
                        ]
                    );

                    $success++;
                } else if ($request->import_type === 'exchange_rates') {
                    // Logic for rates if needed
                    $success++;
                }
            }

            ImportLog::create([
                'import_type' => $request->import_type,
                'file_name' => $fileName,
                'total_rows' => $total,
                'successful_rows' => $success,
                'failed_rows' => $failed,
                'status' => $failed > 0 ? ($success > 0 ? 'partial' : 'failed') : 'success',
                'error_details' => empty($errors) ? null : json_encode($errors),
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('imports.index')
                ->with('success', "Import completed. Success: $success, Failed: $failed.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
