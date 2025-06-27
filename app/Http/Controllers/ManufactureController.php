<?php

namespace App\Http\Controllers;

use App\Models\Master\Manufacture;
use Illuminate\Http\Request;

class ManufactureController extends Controller
{
    //function API endpoint untuk melakukan live search pada produsen obat
    public function searchManufacture(Request $request)
    {
        $query = $request->input('query');
        $manufactures = Manufacture::where('name', 'like', "%{$query}%")->get();

        return response()->json($manufactures);
    }
    public function index()
    {
        $judul = "Produsen Obat";
        $manufactures = Manufacture::paginate(5);
        return view('pages.master.manufacture',compact('judul','manufactures'));
    }
    public function store(Request $request)
    {
        try {
            $validate = $request->validate([
                "name"=> "required|min:3|max:25|string|unique:manufactures,name"
            ], [
                'name.unique' => 'Produsen sudah ada'
            ]);
            Manufacture::create([
                "name"=>$request->name
            ]);
            return back()->with('success','Produsen berhasil dibuat');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (isset($e->errors()['name']) && in_array('Produsen sudah ada', $e->errors()['name'])) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', 'Produsen gagal dibuat')->withInput();
        } catch (\Throwable $e) {
            return back()->with('error','Produsen gagal dibuat');
        }
    }
    public function update(Request $request, Manufacture $manufacture)
    {
        try {
            $validate = $request->validate([
                "name"=> "required|min:3|max:25|string|unique:manufactures,name," . $manufacture->id
            ], [
                'name.unique' => 'Produsen sudah ada'
            ]);
            $manufacture->update($validate);
            return redirect()->back()->with('success','Produsen berhasil diubah');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (isset($e->errors()['name']) && in_array('Produsen sudah ada', $e->errors()['name'])) {
                return back()->withErrors($e->errors())->withInput();
            }
            return back()->with('error', 'Produsen gagal diubah')->withInput();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error','Produsen gagal diubah');
        }
    }
    public function destroy(Manufacture $manufacture)
    {
        try {
            $manufacture->delete();
            return back()->with('success','Produsen berhasil dihapus');
        } catch (\Throwable $e) {
            return back()->with('error','Produsen gagal dihapus');
        }
    }

}
