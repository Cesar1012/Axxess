<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laboratorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Laboratorios
 * 
 * API para gestionar laboratorios farmacéuticos
 */
class LaboratorioController extends Controller
{
    /**
     * Listar todos los laboratorios
     * 
     * @queryParam page integer Número de página. Example: 1
     * @queryParam per_page integer Laboratorios por página. Example: 15
     * @queryParam activo boolean Filtrar por estado. Example: 1
     */
    public function index(Request $request)
    {
        $query = Laboratorio::query();

        // Filtros opcionales
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        if ($request->has('pais_origen')) {
            $query->where('pais_origen', $request->pais_origen);
        }

        // Búsqueda por nombre o NIT
        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre_laboratorio', 'ILIKE', "%{$buscar}%")
                  ->orWhere('nit', 'ILIKE', "%{$buscar}%");
            });
        }

        // Paginación
        $perPage = $request->get('per_page', 15);
        $laboratorios = $query->orderBy('nombre_laboratorio')->paginate($perPage);

        return response()->json($laboratorios, 200);
    }

    /**
     * Crear un nuevo laboratorio
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_laboratorio' => 'required|string|max:150',
            'nit' => 'required|string|max:50|unique:laboratorios,nit',
            'pais_origen' => 'nullable|string|max:50',
            'contacto_nombre' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $laboratorio = Laboratorio::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Laboratorio creado exitosamente',
            'data' => $laboratorio
        ], 201);
    }

    /**
     * Mostrar un laboratorio específico
     */
    public function show($id)
    {
        $laboratorio = Laboratorio::find($id);

        if (!$laboratorio) {
            return response()->json([
                'success' => false,
                'message' => 'Laboratorio no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $laboratorio
        ], 200);
    }

    /**
     * Actualizar un laboratorio
     */
    public function update(Request $request, $id)
    {
        $laboratorio = Laboratorio::find($id);

        if (!$laboratorio) {
            return response()->json([
                'success' => false,
                'message' => 'Laboratorio no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_laboratorio' => 'sometimes|required|string|max:150',
            'nit' => 'sometimes|required|string|max:50|unique:laboratorios,nit,' . $id,
            'pais_origen' => 'nullable|string|max:50',
            'contacto_nombre' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $laboratorio->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Laboratorio actualizado exitosamente',
            'data' => $laboratorio
        ], 200);
    }

    /**
     * Eliminar (desactivar) un laboratorio
     */
    public function destroy($id)
    {
        $laboratorio = Laboratorio::find($id);

        if (!$laboratorio) {
            return response()->json([
                'success' => false,
                'message' => 'Laboratorio no encontrado'
            ], 404);
        }

        // Desactivar en lugar de eliminar
        $laboratorio->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Laboratorio desactivado exitosamente'
        ], 200);
    }

    /**
     * Buscar laboratorio por NIT
     */
    public function buscarPorNit($nit)
    {
        $laboratorio = Laboratorio::where('nit', $nit)->first();

        if (!$laboratorio) {
            return response()->json([
                'success' => false,
                'message' => 'Laboratorio no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $laboratorio
        ], 200);
    }
}
