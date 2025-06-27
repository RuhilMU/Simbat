<?php

namespace App\Http\Controllers;

use App\Models\Inventory\Warehouse;
use App\Models\Master\Category;
use App\Models\Master\Drug;
use App\Models\Master\Manufacture;
use App\Models\Master\Repack;
use App\Models\Master\Variant;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DrugController extends Controller
{
    //function API endpoint untuk melakukan live search pada master obat berdasarkan nama dan kode
    public function searchDrug(Request $request)
    {
        $query = $request->input('query');
        $drugs = Drug::where('name', 'like', "%{$query}%")->orWhere('code', 'like', "%{$query}%")->get();

        return response()->json($drugs);
    }
    //function API endpoint untuk melakukan live search obat pada obat masuk berdasarkan nama
    public function getSuggestions(Request $request)
    {
        $query = $request->input('query');
        $drugs = Drug::where('name', 'like', "%{$query}%")->with('warehouse')->get();

        return response()->json($drugs);
    }
    //function API endpoint untuk melakukan live search semua repack pada checkout
    public function getRepacks(Request $request)
    {
        $query = $request->input('query');
        $source = $request->input('source', 'warehouse');
        
        $drugs = Repack::where('name', 'like', "%{$query}%")->get();
        $drugs = $drugs->map(function ($drug) use ($source) {
            $drug->stock = $source === 'clinic' ? $drug->clinic_stock() : $drug->stock();
            $drug->drug = $drug->drug();
            return $drug;
        });

        return response()->json($drugs);
    }

    public function index(): View
    {
        $drugs = Drug::paginate(5);
        $judul = "Nama Obat";
        return view('pages.master.drug', compact('drugs', 'judul'));
    }
    public function create()
    {
        $categories = Category::all();
        $variants = Variant::all();
        $manufactures = Manufacture::all();
        $judul = "Input Obat";
        return view('pages.master.createDrug', compact('categories', 'variants', 'manufactures', 'judul'));
    }
    public function store(Request $request)
    {
        try {
            $category = Category::find($request->category_id);
            //membuat kode untuk obat yang dibuat
            $request["code"] = $this->generateCode($category);
            
            $validate = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'variant_id' => 'required|exists:variants,id',
                'manufacture_id' => 'required|exists:manufactures,id',
                'name' => 'required|string|min:3|max:255|unique:drugs,name',
                'code' => 'unique:drugs,code',
                'last_price' => 'nullable|integer|min:0',
                'last_discount' => 'nullable|integer|min:0',
                'maximum_capacity' => 'required|integer|min:1',
                'minimum_capacity' => 'required|integer|min:0',
                'pack_quantity' => 'required|integer|min:1',
                'pack_margin' => 'required|integer|min:0',
                'piece_quantity' => 'required|integer|min:1',
                'piece_margin' => 'required|integer|min:0',
                'piece_netto' => 'required|integer|min:1',
                'piece_unit' => 'required|in:ml,mg,butir'
            ], [
                'name.unique' => 'Nama obat sudah ada',
                'code.unique' => 'Kode obat sudah ada'
            ]);
            
            $drug = Drug::create($validate);
            //membuat repack dan stok default untuk obat yang dibuat
            $drug->default_repacks();
            $drug->default_stock();
            return redirect()->route('master.drug.edit', $drug->id)->with('success', 'Obat berhasil dibuat');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $allErrors = $e->errors();
            $allAreDuplicates = true;
            
            foreach ($allErrors as $field => $errors) {
                foreach ($errors as $error) {
                    if ($error !== 'Nama obat sudah ada' && $error !== 'Kode obat sudah ada') {
                        $allAreDuplicates = false;
                        break 2;
                    }
                }
            }
            
            if ($allAreDuplicates) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', 'Obat gagal dibuat')->withInput();
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Obat gagal dibuat');
        }
    }
    public function edit(Drug $drug)
    {
        $categories = Category::all();
        $variants = Variant::all();
        $manufactures = Manufacture::all();
        $judul = "Edit Obat " . $drug->name;
        $repacks = $drug->repacks();
        return view('pages.master.editDrug', compact('categories', 'variants', 'manufactures', 'drug', 'judul', 'repacks'));
    }
    public function update(Request $request, Drug $drug)
    {
        try {
            $validate = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'variant_id' => 'required|exists:variants,id',
                'manufacture_id' => 'required|exists:manufactures,id',
                'name' => 'required|string|min:3|max:255|unique:drugs,name,' . $drug->id,
                'code' => 'unique:drugs,code,' . $drug->id,
                'last_price' => 'nullable|integer|min:0',
                'last_discount' => 'nullable|integer|min:0',
                'maximum_capacity' => 'required|integer|min:1',
                'minimum_capacity' => 'required|integer|min:0',
                'pack_quantity' => 'required|integer|min:1',
                'pack_margin' => 'required|integer|min:0',
                'piece_quantity' => 'required|integer|min:1',
                'piece_margin' => 'required|integer|min:0',
                'piece_netto' => 'required|integer|min:1',
                'piece_unit' => 'required|in:ml,mg,butir'
            ], [
                'name.unique' => 'Nama obat sudah ada',
                'code.unique' => 'Kode obat sudah ada'
            ]);
            
            $drug->update($validate);
            $repacks = $drug->repacks();
            //melakukan update data harga terhadap semua data repack
            foreach ($repacks as $item) {
                $item->update_price();
            }
            return redirect()->back()->with('success', 'Berhasil mengubah data obat');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $allErrors = $e->errors();
            $allAreDuplicates = true;
            
            foreach ($allErrors as $field => $errors) {
                foreach ($errors as $error) {
                    if ($error !== 'Nama obat sudah ada' && $error !== 'Kode obat sudah ada') {
                        $allAreDuplicates = false;
                        break 2;
                    }
                }
            }
            
            if ($allAreDuplicates) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', 'Gagal mengubah data obat')->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data obat');
        }
    }
    public function repack(Request $request, Drug $drug, Repack $repack = null)
    {
        if ($request->isMethod('DELETE')) {
            try {
                //melakukan pengecekan apakah repack adalah repack default (1 pack dan 1pcs)
                if ($repack->quantity != $drug->piece_quantity * $drug->piece_netto && $repack->quantity != $drug->piece_netto) {
                    if ($repack->stock() > 0 || $repack->clinic_stock() > 0) {
                        return back()->with('error', 'Tidak dapat menghapus repack yang masih memiliki stok');
                    }
                    $repack->delete();
                    return back()->with('success', 'Berhasil menghapus repack');
                }
                return back()->with('error', 'Tidak dapat menghapus repack default');
            } catch (\Throwable $e) {
                return back()->with('error', 'Gagal menghapus repack');
            }
        } else {
            try {
                $validate = $request->validate([
                    'quantity' => 'required|numeric|min:1',
                    'piece_unit' => 'required|in:pack,pcs',
                    'margin' => 'required|numeric|min:0'
                ]);

                if ($drug->last_price == 0) {
                    return back()->with('error', 'Tidak dapat membuat repack: Obat belum memiliki harga')->withInput();
                }

                $quantity = $request->quantity;
                if ($request->piece_unit == "pcs") {
                    $quantity = $request->quantity * $drug->piece_netto;
                }

                $existingRepack = Repack::where('drug_id', $drug->id)
                    ->where('quantity', $quantity)
                    ->first();
                
                if ($existingRepack) {
                    return back()->with('error', 'Repack dengan jumlah tersebut sudah ada')->withInput();
                }

                Repack::create([
                    "drug_id" => $drug->id,
                    "name" => $drug->name . " " . $request->quantity . " " . $request->piece_unit,
                    "quantity" => $quantity,
                    "margin" => $request->margin,
                    "price" => $drug->calculate_price($quantity, $request->margin)
                ]);
                return back()->with('success', 'Berhasil membuat repack');
            } catch (\Illuminate\Validation\ValidationException $e) {
                return back()->with('error', 'Gagal membuat repack')->withInput();
            } catch (\Throwable $e) {
                return back()->with('error', 'Gagal membuat repack')->withInput();
            }
        }
    }
    public function destroy(Drug $drug)
    {
        try {
            //melakukan pengecekan bahwa obat yang dihapus haruslah tidak memiliki stok di gudang maupun klinik
            if ($drug->warehouse->quantity > 0 || $drug->clinic->quantity > 0) {
                throw new Exception('Division by zero.');
            }
            $drug->delete();
            return redirect()->back()->with('success', 'Obat berhasil dihapus');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Obat gagal dihapus');
        }
    }
    //fungsi untuk membuat kode obat berdasarkan kategori
    function generateCode(Category $category): string
    {
        $lastDrug = $category->drugs()->last();
        if (!$lastDrug) {
            $nextNumber = 1;
        } else {
            $lastNumber = (int) substr($lastDrug->code, -4);
            $nextNumber = $lastNumber + 1;
        }
        $paddedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        return $category->code . $paddedNumber;
    }
}
