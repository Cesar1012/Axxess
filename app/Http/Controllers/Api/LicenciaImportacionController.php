<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LicenciaImportacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @group Licencias de Importación
 * 
 * API para gestionar licencias de importación
 */
class LicenciaImportacionController extends Controller
{
    public function index(Request $request)
    {
        $query = LicenciaImportacion::with(['producto']);

        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tipo_licencia')) {
            $query->where('tipo_licencia', $request->tipo_licencia);
        }

        if ($request->has('codigo_paciente')) {
            $query->where('codigo_paciente', $request->codigo_paciente);
        }

        // Filtrar próximas a vencer
        if ($request->has('proximas_vencer')) {
            $dias = $request->get('dias', 30);
            $fechaLimite = Carbon::now()->addDays($dias);
            $query->where('fecha_vencimiento', '<=', $fechaLimite)
                  ->where('fecha_vencimiento', '>=', Carbon::now());
        }

        $perPage = $request->get('per_page', 15);
        $licencias = $query->orderBy('fecha_vencimiento')->paginate($perPage);

        return response()->json($licencias, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_licencia' => 'required|string|max:100|unique:licencias_importacion,numero_licencia',
            'producto_id' => 'required|exists:productos,id',
            'cantidad_autorizada' => 'required|integer|min:1',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_emision',
            'lote_autorizado' => 'nullable|string|max:50',
            'fecha_vencimiento_lote' => 'nullable|date',
            'codigo_paciente' => 'nullable|string|max:50',
            'tipo_licencia' => 'required|string|in:inicial,prorroga_1,prorroga_2',
            'estado' => 'nullable|string|in:vigente,vencida,agotada,cancelada',
            'documento_soporte' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['cantidad_importada'] = 0;
        $data['saldo_pendiente'] = $request->cantidad_autorizada;
        $data['estado'] = $request->estado ?? 'vigente';

        $licencia = LicenciaImportacion::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Licencia de importación creada exitosamente',
            'data' => $licencia->load('producto')
        ], 201);
    }

    public function show($id)
    {
        $licencia = LicenciaImportacion::with(['producto', 'importaciones'])->find($id);

        if (!$licencia) {
            return response()->json(['success' => false, 'message' => 'Licencia no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $licencia], 200);
    }

    public function update(Request $request, $id)
    {
        $licencia = LicenciaImportacion::find($id);
        if (!$licencia) {
            return response()->json(['success' => false, 'message' => 'Licencia no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'cantidad_importada' => 'sometimes|integer|min:0',
            'estado' => 'sometimes|string|in:vigente,vencida,agotada,cancelada',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Actualizar saldo pendiente si se modifica cantidad importada
        if ($request->has('cantidad_importada')) {
            $request->merge([
                'saldo_pendiente' => $licencia->cantidad_autorizada - $request->cantidad_importada
            ]);
        }

        $licencia->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Licencia actualizada exitosamente',
            'data' => $licencia->load('producto')
        ], 200);
    }

    public function destroy($id)
    {
        $licencia = LicenciaImportacion::find($id);
        if (!$licencia) {
            return response()->json(['success' => false, 'message' => 'Licencia no encontrada'], 404);
        }

        if ($licencia->cantidad_importada > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar, ya tiene importaciones'], 400);
        }

        $licencia->update(['estado' => 'cancelada']);
        return response()->json(['success' => true, 'message' => 'Licencia cancelada'], 200);
    }

    public function proximasAVencer(Request $request)
    {
        $dias = $request->get('dias', 30);
        $fechaLimite = Carbon::now()->addDays($dias);

        $licencias = LicenciaImportacion::with(['producto'])
            ->where('fecha_vencimiento', '<=', $fechaLimite)
            ->where('fecha_vencimiento', '>=', Carbon::now())
            ->where('estado', 'vigente')
            ->orderBy('fecha_vencimiento')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $licencias,
            'total' => $licencias->count()
        ], 200);
    }
}
