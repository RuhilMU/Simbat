<?php

namespace App\Http\Controllers;

use App\Models\Master\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    //function API endpoint untuk live search pada master vendor obat
    public function searchVendor(Request $request)
    {
        $query = $request->input('query');
        $vendors = Vendor::where('name', 'like', "%{$query}%")->get();

        return response()->json($vendors);
    }
    public function index()
    {
        $judul = "Vendor Obat";
        $vendors = Vendor::paginate(5);
        return view('pages.master.vendor',compact('judul','vendors'));
    }
    public function store(Request $request)
    {
        try {
            $validate = $request->validate([
                "name"=> "required|min:3|max:25|string|unique:vendors,name",
                "phone"=>"required|max:14",
                "address"=>"required|string|max:255",
            ], [
                'name.unique' => 'Vendor sudah ada'
            ]);
            Vendor::create($validate);
            return redirect()->back()->with('success','Vendor berhasil dibuat');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $allErrors = $e->errors();
            $allAreDuplicates = true;
            
            foreach ($allErrors as $field => $errors) {
                foreach ($errors as $error) {
                    if ($error !== 'Vendor sudah ada') {
                        $allAreDuplicates = false;
                        break 2;
                    }
                }
            }
            
            if ($allAreDuplicates) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', 'Vendor gagal dibuat')->withInput();
        } catch (\Throwable $th) {
            return redirect()->back()->with('error','Vendor gagal dibuat');
        }
    }
    public function update(Request $request, Vendor $vendor)
    {
        try {
            $validate = $request->validate([
                "name"=> "required|min:3|max:25|string|unique:vendors,name," . $vendor->id,
                "phone"=>"required|max:14",
                "address"=>"required|string|max:255",
            ], [
                'name.unique' => 'Vendor sudah ada'
            ]);
            $vendor->update($validate);
            return redirect()->back()->with('success','Vendor berhasil diubah');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $allErrors = $e->errors();
            $allAreDuplicates = true;
            
            foreach ($allErrors as $field => $errors) {
                foreach ($errors as $error) {
                    if ($error !== 'Vendor sudah ada') {
                        $allAreDuplicates = false;
                        break 2;
                    }
                }
            }
            
            if ($allAreDuplicates) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', 'Vendor gagal diubah')->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error','Vendor gagal diubah');
        }
    }
    public function destroy(Vendor $vendor)
    {
        try {
            $vendor->delete();
            return back()->with('success', 'Vendor berhasil dihapus');
        } catch (\Throwable $e) {
            return back()->with('error', 'Vendor gagal dihapus');
        }
    }
}
