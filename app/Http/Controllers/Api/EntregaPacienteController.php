<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntregaPaciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EntregaPacienteController extends Controller
{
    public function index(Request $request)
    {
        $query = EntregaPaciente::with(['paciente', 'despacho', 'autorizacionInvima', 'producto', 'lote']);

        if ($request->has('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
        }

        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_entrega', '>=', $request->fecha_desde);
        }

        $perPage = $request->get('per_page', 15);
        $entregas = $query->orderBy('fecha_entrega', 'desc')->paginate($perPage);
        return response()->json($entregas, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'despacho_id' => 'nullable|exists:despachos,id',
            'autorizacion_invima_id' => 'nullable|exists:autorizaciones_invima,id',
            'producto_id' => 'required|exists:productos,id',
            'lote_id' => 'required|exists:lotes,id',
            'cantidad_viales' => 'required|integer|min:1',
            'fecha_entrega' => 'required|date',
            'fecha_aplicacion' => 'nullable|date',
            'viales_utilizados_terapia' => 'nullable|integer|min:0',
            'requirio_acondicionamiento' => 'boolean',
            'insumos_nevera' => 'boolean',
            'insumos_gel' => 'boolean',
            'costo_insumos' => 'nullable|numeric|min:0',
            'comision_entrega' => 'nullable|numeric|min:0',
            'documento_soporte' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $entrega = EntregaPaciente::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Entrega registrada exitosamente',
            'data' => $entrega->load(['paciente', 'producto', 'lote'])
        ], 201);
    }

    public function show($id)
    {
        $entrega = EntregaPaciente::with([
            'paciente',
            'despacho',
            'autorizacionInvima',
            'producto',
            'lote'
        ])->find($id);

        if (!$entrega) {
            return response()->json(['success' => false, 'message' => 'Entrega no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $entrega], 200);
    }

    public function update(Request $request, $id)
    {
        $entrega = EntregaPaciente::find($id);
        if (!$entrega) {
            return response()->json(['success' => false, 'message' => 'Entrega no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'fecha_aplicacion' => 'nullable|date',
            'viales_utilizados_terapia' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $entrega->update($request->all());
        return response()->json(['success' => true, 'message' => 'Entrega actualizada', 'data' => $entrega], 200);
    }

    public function destroy($id)
    {
        $entrega = EntregaPaciente::find($id);
        if (!$entrega) {
            return response()->json(['success' => false, 'message' => 'Entrega no encontrada'], 404);
        }

        $entrega->delete();
        return response()->json(['success' => true, 'message' => 'Entrega eliminada'], 200);
    }

    public function porPaciente($pacienteId)
    {
        $entregas = EntregaPaciente::with(['producto', 'lote', 'autorizacionInvima'])
            ->where('paciente_id', $pacienteId)
            ->orderBy('fecha_entrega', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $entregas,
            'total_entregas' => $entregas->count()
        ], 200);
    }
}
