<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionSistemaController extends Controller
{
    public function index(Request $request)
    {
        $query = ConfiguracionSistema::query();

        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        $configuraciones = $query->orderBy('parametro')->get();
        return response()->json($configuraciones, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parametro' => 'required|string|max:100|unique:configuracion_sistema,parametro',
            'valor' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'modulo' => 'nullable|string|max:20',
            'tipo_dato' => 'nullable|string|in:string,integer,boolean,float,json'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $config = ConfiguracionSistema::create($request->all());
        return response()->json(['success' => true, 'message' => 'Configuración creada', 'data' => $config], 201);
    }

    public function show($id)
    {
        $config = ConfiguracionSistema::find($id);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }
        return response()->json(['success' => true, 'data' => $config], 200);
    }

    public function update(Request $request, $id)
    {
        $config = ConfiguracionSistema::find($id);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'valor' => 'nullable|string',
            'descripcion' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $config->update($request->only(['valor', 'descripcion']));
        return response()->json(['success' => true, 'message' => 'Configuración actualizada', 'data' => $config], 200);
    }

    public function destroy($id)
    {
        $config = ConfiguracionSistema::find($id);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }

        $config->delete();
        return response()->json(['success' => true, 'message' => 'Configuración eliminada'], 200);
    }

    public function getByParametro($parametro)
    {
        $config = ConfiguracionSistema::where('parametro', $parametro)->first();
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Configuración no encontrada'], 404);
        }
        return response()->json(['success' => true, 'data' => $config], 200);
    }
}
