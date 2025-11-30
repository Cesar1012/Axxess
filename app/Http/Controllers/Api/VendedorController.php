<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendedor::with('usuario');

        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        if ($request->has('zona_asignada')) {
            $query->where('zona_asignada', $request->zona_asignada);
        }

        $perPage = $request->get('per_page', 15);
        $vendedores = $query->orderBy('nombre_completo')->paginate($perPage);

        return response()->json($vendedores, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'nullable|exists:usuarios,id',
            'codigo_vendedor' => 'required|string|max:20|unique:vendedores',
            'nombre_completo' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'zona_asignada' => 'nullable|string|max:100',
            'comision_porcentaje' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendedor = Vendedor::create($request->all());

        return response()->json([
            'message' => 'Vendedor creado exitosamente',
            'vendedor' => $vendedor
        ], 201);
    }

    public function show($id)
    {
        $vendedor = Vendedor::with(['usuario', 'pedidos', 'ventas'])->find($id);

        if (!$vendedor) {
            return response()->json(['message' => 'Vendedor no encontrado'], 404);
        }

        return response()->json($vendedor, 200);
    }

    public function update(Request $request, $id)
    {
        $vendedor = Vendedor::find($id);

        if (!$vendedor) {
            return response()->json(['message' => 'Vendedor no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo_vendedor' => 'sometimes|string|max:20|unique:vendedores,codigo_vendedor,' . $id,
            'nombre_completo' => 'sometimes|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'comision_porcentaje' => 'nullable|numeric|min:0|max:100',
            'activo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendedor->update($request->all());

        return response()->json([
            'message' => 'Vendedor actualizado exitosamente',
            'vendedor' => $vendedor
        ], 200);
    }

    public function destroy($id)
    {
        $vendedor = Vendedor::find($id);

        if (!$vendedor) {
            return response()->json(['message' => 'Vendedor no encontrado'], 404);
        }

        $vendedor->update(['activo' => false]);

        return response()->json(['message' => 'Vendedor desactivado exitosamente'], 200);
    }
}
