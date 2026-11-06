<?php

namespace App\Http\Controllers;

use App\Models\AgenteSoporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AgenteSoporteAdminController extends Controller
{
    public function index(Request $request)
    {
        $agentes = AgenteSoporte::query()
            ->when($request->filled('busqueda'), function ($q) use ($request) {
                $texto = '%' . trim($request->busqueda) . '%';
                $correo = '%' . $this->normalizarEmail($request->busqueda) . '%';
                $q->where(function ($w) use ($texto, $correo) {
                    $w->where('nombre', 'like', $texto)
                        ->orWhere('email', 'like', $correo);
                });
            })
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return view('admin.soporte_agentes.index', compact('agentes'));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        AgenteSoporte::create([
            'nombre' => $datos['nombre'],
            'email' => $datos['email'],
            'password' => Hash::make($request->password),
            'activo' => $request->has('activo'),
        ]);

        return back()->with('ok', 'Agente de soporte registrado. Ya puede entrar con su correo y contraseña.');
    }

    public function update(Request $request, $id)
    {
        $agente = AgenteSoporte::findOrFail($id);
        $datos = $this->validar($request, $agente->id);

        $agente->nombre = $datos['nombre'];
        $agente->email = $datos['email'];
        $agente->activo = $request->has('activo');

        // El campo llega vacio cuando el admin no quiere tocarla. Si se
        // re-hasheara siempre, el hash guardado volveria a hashearse y la
        // contraseña del agente dejaria de servir.
        if ($request->filled('password')) {
            $agente->password = Hash::make($request->password);
        }

        $agente->save();

        return back()->with('ok', $request->filled('password')
            ? 'Agente actualizado y contraseña cambiada.'
            : 'Agente actualizado.');
    }

    public function toggle($id)
    {
        $agente = AgenteSoporte::findOrFail($id);
        $agente->activo = !$agente->activo;
        $agente->save();

        return back()->with('ok', $agente->activo
            ? 'Agente activado: ya puede iniciar sesión.'
            : 'Agente desactivado: no podrá iniciar sesión.');
    }

    public function destroy($id)
    {
        $agente = AgenteSoporte::findOrFail($id);
        $nombre = $agente->nombre;
        $agente->delete();

        return back()->with('ok', 'Agente ' . $nombre . ' eliminado. Los comentarios que ya había escrito se conservan.');
    }

    /**
     * Validación compartida por alta y edición.
     *
     * El email se normaliza a minúsculas porque el login lo compara exacto:
     * si se guardara "Ana@Correo.com" el agente no podría entrar nunca.
     */
    private function validar(Request $request, $ignorarId = null)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('soporte_agentes', 'email')->ignore($ignorarId),
            ],
            'password' => [$ignorarId === null ? 'required' : 'nullable', 'string', 'min:8'],
        ]);

        return [
            'nombre' => trim($request->nombre),
            'email' => $this->normalizarEmail($request->email),
        ];
    }

    private function normalizarEmail($email)
    {
        return mb_strtolower(trim($email));
    }
}
