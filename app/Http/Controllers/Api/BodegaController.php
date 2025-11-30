<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Bodegas
 * 
 * API para gestionar bodegas de almacenamiento
 */
class BodegaController extends Controller
{
    /**
     * Listar todas las bodegas
     */
    public function index(Request $request)
    {
        $query = Bodega::query();

        // Filtros opcionales
        if ($request->has('activa')) {
            $query->where('activa', $request->activa);
        }

        if ($request->has('ubicacion_ciudad')) {
            $query->where('ubicacion_ciudad', 'ILIKE', "%{$request->ubicacion_ciudad}%");
        }

        // Búsqueda por nombre
        if ($request->has('buscar')) {
            $query->where('nombre_bodega', 'ILIKE', "%{$request->buscar}%");
        }

        $perPage = $request->get('per_page', 15);
        $bodegas = $query->orderBy('nombre_bodega')->paginate($perPage);

        return response()->json($bodegas, 200);
    }

    /**
     * Crear una nueva bodega
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_bodega' => 'required|string|max:100',
            'ubicacion_ciudad' => 'required|string|max:100',
            'direccion' => 'nullable|string',
            'activa' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bodega = Bodega::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Bodega creada exitosamente',
            'data' => $bodega
        ], 201);
    }

    /**
     * Mostrar una bodega específica
     */
    public function show($id)
    {
        $bodega = Bodega::with(['lotes.producto'])->find($id);

        if (!$bodega) {
            return response()->json([
                'success' => false,
                'message' => 'Bodega no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bodega
        ], 200);
    }

    /**
     * Actualizar una bodega
     */
    public function update(Request $request, $id)
    {
        $bodega = Bodega::find($id);

        if (!$bodega) {
            return response()->json([
                'success' => false,
                'message' => 'Bodega no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_bodega' => 'sometimes|required|string|max:100',
            'ubicacion_ciudad' => 'sometimes|required|string|max:100',
            'direccion' => 'nullable|string',
            'activa' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bodega->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Bodega actualizada exitosamente',
            'data' => $bodega
        ], 200);
    }

    /**
     * Eliminar (desactivar) una bodega
     */
    public function destroy($id)
    {
        $bodega = Bodega::find($id);

        if (!$bodega) {
            return response()->json([
                'success' => false,
                'message' => 'Bodega no encontrada'
            ], 404);
        }

        // Verificar si tiene lotes asociados
        if ($bodega->lotes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede desactivar la bodega porque tiene lotes asociados'
            ], 400);
        }

        $bodega->update(['activa' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Bodega desactivada exitosamente'
        ], 200);
    }

    /**
     * Obtener inventario de una bodega
     */
    public function inventario($id)
    {
        $bodega = Bodega::with([
            'lotes' => function($query) {
                $query->where('estado', 'disponible')
                      ->with('producto');
            }
        ])->find($id);

        if (!$bodega) {
            return response()->json([
                'success' => false,
                'message' => 'Bodega no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'bodega' => $bodega->nombre_bodega,
                'lotes' => $bodega->lotes,
                'total_lotes' => $bodega->lotes->count(),
                'total_productos' => $bodega->lotes->sum('cantidad_actual')
            ]
        ], 200);
    }
}
