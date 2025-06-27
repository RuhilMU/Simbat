<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\InventoryUpload;
use App\Models\Master\Drug;
use App\Models\Master\Vendor;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;

class InventoryUploadController extends Controller
{
    public function import(Request $request)
    {
        // Validasi file harus xlsx atau xls
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        // Cek jika file tidak ada
        if (!$request->hasFile('file')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Tidak ada file yang diunggah.'], 422);
            }
            return back()->with('error', 'Tidak ada file yang diunggah.');
        }

        // Mendapatkan file yang di-upload
        $file = $request->file('file');

        try {
            // Hapus data sesi sebelumnya jika ada
            Session::forget('imported_data');

            // Import file menggunakan class InventoryUpload
            Excel::import(new InventoryUpload, $file);
            $importedData = Session::get('imported_data');

            // Cek jika data kosong
            if (empty($importedData) || (is_object($importedData) && $importedData->isEmpty())) {
                if ($request->ajax()) {
                    return response()->json(['error' => 'Tidak ada data valid yang berhasil diimpor.'], 422);
                }
                return back()->with('warning', 'Tidak ada data valid yang berhasil diimpor.');
            }

            $drugs = Drug::all();
            $judul = "Tambah Barang";

            $processedData = $importedData;
            Session::put('imported_data', $processedData);
            Session::save();

            if ($request->ajax()) {
                return response()->json(['data' => $processedData->values()]);
            }

            return view("pages.inventory.addStuff", compact('drugs', 'judul', 'importedData'));

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()], 500);
            }
            // Tangkap error dan tampilkan pesan
            return back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}
