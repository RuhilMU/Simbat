<?php

namespace App\Http\Controllers;

use App\Models\Master\Variant;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    //function API endpoint untuk live search pada master varian obat
    public function searchVariant(Request $request)
    {
        $query = $request->input('query');
        $variants = Variant::where('name', 'like', "%{$query}%")->get();

        return response()->json($variants);
    }
    public function index()
    {
        $judul = "Jenis Obat";
        $variants = Variant::paginate(5);
        return view('pages.master.variant',compact('judul','variants'));
    }
    public function store(Request $request)
    {
        try {
            $validate = $request->validate([
                "name"=> "required|min:3|max:25|string|unique:variants,name"
            ], [
                'name.unique' => 'Jenis obat sudah ada'
            ]);
            Variant::create([
                "name"=>$request->name
            ]);
            return back()->with('success','Jenis obat berhasil dibuat');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (isset($e->errors()['name']) && in_array('Jenis obat sudah ada', $e->errors()['name'])) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', 'Jenis obat gagal dibuat')->withInput();
        } catch (\Throwable $e) {
            return back()->with('error','Jenis obat gagal dibuat');
        }
    }
    public function update(Request $request, Variant $variant)
    {
        try {
            $validate = $request->validate([
                "name"=> "required|min:3|max:25|string|unique:variants,name," . $variant->id
            ], [
                'name.unique' => 'Jenis obat sudah ada'
            ]);
            $variant->update($validate);
            return redirect()->back()->with('success','Jenis berhasil diubah');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (isset($e->errors()['name']) && in_array('Jenis obat sudah ada', $e->errors()['name'])) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', 'Jenis gagal diubah')->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error','Jenis gagal diubah');
        }
    }
    public function destroy(Variant $variant)
    {
        try {
            $variant->delete();
            return back()->with('success', 'Jenis obat berhasil dihapus');
        } catch (\Throwable $e) {
            return back()->with('error', 'Jenis obat gagal dihapus');
        }
    }
}
