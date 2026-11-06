<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();
        return view('admin.proveedores.index', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'correo' => 'nullable|email',
        ]);

        Proveedor::create($request->only('nombre', 'descripcion', 'contacto', 'correo', 'telefono') + ['activo' => $request->has('activo')]);

        return back()->with('ok', 'Proveedor registrado');
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $request->validate([
            'nombre' => 'required',
            'correo' => 'nullable|email',
        ]);

        $proveedor->update($request->only('nombre', 'descripcion', 'contacto', 'correo', 'telefono') + ['activo' => $request->has('activo')]);

        return back()->with('ok', 'Proveedor actualizado');
    }

    public function toggle($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->activo = !$proveedor->activo;
        $proveedor->save();

        return back();
    }

    public function destroy($id)
    {
        Proveedor::findOrFail($id)->delete();

        return back()->with('ok', 'Proveedor eliminado');
    }
}
