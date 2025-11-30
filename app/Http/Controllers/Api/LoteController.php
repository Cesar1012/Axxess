<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @group Lotes
 * 
 * API para gestionar lotes de productos
 */
class LoteController extends Controller
{
    /**
     * Listar todos los lotes
     */
    public function index(Request $request)
    {
        $query = Lote::with(['producto', 'bodega', 'laboratorio']);

        // Filtros opcionales
        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->has('bodega_id')) {
            $query->where('bodega_id', $request->bodega_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('acondicionado')) {
            $query->where('acondicionado', $request->acondicionado);
        }

        // Filtrar por próximos a vencer (días)
        if ($request->has('dias_vencimiento')) {
            $dias = $request->dias_vencimiento;
            $fechaLimite = Carbon::now()->addDays($dias);
            $query->where('fecha_vencimiento', '<=', $fechaLimite)
                  ->where('fecha_vencimiento', '>=', Carbon::now());
        }

        // Búsqueda por número de lote
        if ($request->has('buscar')) {
            $query->where('numero_lote', 'ILIKE', "%{$request->buscar}%");
        }

        $perPage = $request->get('per_page', 15);
        $lotes = $query->orderBy('fecha_vencimiento')->paginate($perPage);

        return response()->json($lotes, 200);
    }

    /**
     * Crear un nuevo lote
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|exists:productos,id',
            'numero_lote' => 'required|string|max:50',
            'fecha_fabricacion' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_fabricacion',
            'cantidad_inicial' => 'required|integer|min:1',
            'bodega_id' => 'nullable|exists:bodegas,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'deposito_llegada' => 'nullable|string|max:100',
            'acondicionado' => 'boolean',
            'estado' => 'nullable|string|in:disponible,reservado,agotado,vencido',
            'foto_producto' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no exista el mismo lote para el mismo producto
        $existeLote = Lote::where('producto_id', $request->producto_id)
                          ->where('numero_lote', $request->numero_lote)
                          ->exists();

        if ($existeLote) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un lote con este número para este producto'
            ], 400);
        }

        $data = $request->all();
        $data['cantidad_actual'] = $request->cantidad_inicial;
        $data['estado'] = $request->estado ?? 'disponible';

        $lote = Lote::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Lote creado exitosamente',
            'data' => $lote->load(['producto', 'bodega', 'laboratorio'])
        ], 201);
    }

    /**
     * Mostrar un lote específico
     */
    public function show($id)
    {
        $lote = Lote::with([
            'producto',
            'bodega',
            'laboratorio',
            'detalleDespachos.despacho'
        ])->find($id);

        if (!$lote) {
            return response()->json([
                'success' => false,
                'message' => 'Lote no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $lote
        ], 200);
    }

    /**
     * Actualizar un lote
     */
    public function update(Request $request, $id)
    {
        $lote = Lote::find($id);

        if (!$lote) {
            return response()->json([
                'success' => false,
                'message' => 'Lote no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'numero_lote' => 'sometimes|required|string|max:50',
            'fecha_fabricacion' => 'sometimes|required|date',
            'fecha_vencimiento' => 'sometimes|required|date',
            'cantidad_actual' => 'sometimes|required|integer|min:0',
            'bodega_id' => 'nullable|exists:bodegas,id',
            'laboratorio_id' => 'nullable|exists:laboratorios,id',
            'deposito_llegada' => 'nullable|string|max:100',
            'acondicionado' => 'boolean',
            'estado' => 'nullable|string|in:disponible,reservado,agotado,vencido',
            'foto_producto' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $lote->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lote actualizado exitosamente',
            'data' => $lote->load(['producto', 'bodega', 'laboratorio'])
        ], 200);
    }

    /**
     * Eliminar un lote
     */
    public function destroy($id)
    {
        $lote = Lote::find($id);

        if (!$lote) {
            return response()->json([
                'success' => false,
                'message' => 'Lote no encontrado'
            ], 404);
        }

        // Verificar si tiene movimientos asociados
        if ($lote->detalleDespachos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el lote porque tiene movimientos asociados'
            ], 400);
        }

        $lote->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lote eliminado exitosamente'
        ], 200);
    }

    /**
     * Obtener lotes próximos a vencer
     */
    public function proximosAVencer(Request $request)
    {
        $dias = $request->get('dias', 90); // Por defecto 90 días
        $fechaLimite = Carbon::now()->addDays($dias);


        $lotes = Lote::with(['producto', 'bodega'])
            ->where('fecha_vencimiento', '<=', $fechaLimite)
            ->where('fecha_vencimiento', '>=', Carbon::now())
            ->where('estado', 'disponible')
            ->orderBy('fecha_vencimiento')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lotes,
            'total' => $lotes->count(),
            'dias_limite' => $dias
        ], 200);
    }

    /**
     * Obtener lotes vencidos
     */
    public function vencidos()
    {
        $lotes = Lote::with(['producto', 'bodega'])
            ->where('fecha_vencimiento', '<', Carbon::now())
            ->where('cantidad_actual', '>', 0)
            ->orderBy('fecha_vencimiento', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lotes,
            'total' => $lotes->count()
        ], 200);
    }
}
