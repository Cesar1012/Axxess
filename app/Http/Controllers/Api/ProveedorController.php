<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Proveedores
 * 
 * API para gestionar proveedores
 */
class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::query();

        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        if ($request->has('ciudad')) {
            $query->where('ciudad', 'ILIKE', "%{$request->ciudad}%");
        }

        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre_proveedor', 'ILIKE', "%{$buscar}%")
                  ->orWhere('nit', 'ILIKE', "%{$buscar}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $proveedores = $query->orderBy('nombre_proveedor')->paginate($perPage);

        return response()->json($proveedores, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_proveedor' => 'required|string|max:150',
            'nit' => 'required|string|max:50|unique:proveedores,nit',
            'contacto_nombre' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $proveedor = Proveedor::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Proveedor creado exitosamente',
            'data' => $proveedor
        ], 201);
    }

    public function show($id)
    {
        $proveedor = Proveedor::with(['compras'])->find($id);

        if (!$proveedor) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $proveedor
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_proveedor' => 'sometimes|required|string|max:150',
            'nit' => 'sometimes|required|string|max:50|unique:proveedores,nit,' . $id,
            'contacto_nombre' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $proveedor->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Proveedor actualizado exitosamente',
            'data' => $proveedor
        ], 200);
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        }

        $proveedor->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Proveedor desactivado exitosamente'
        ], 200);
    }
}
