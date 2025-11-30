<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutorizacionInvima;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AutorizacionInvimaController extends Controller
{
    public function index(Request $request)
    {
        $query = AutorizacionInvima::with(['paciente', 'producto']);

        if ($request->has('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
        }

        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $perPage = $request->get('per_page', 15);
        $autorizaciones = $query->orderBy('fecha_vencimiento')->paginate($perPage);

        return response()->json($autorizaciones, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_autorizacion' => 'required|string|max:100|unique:autorizaciones_invima,numero_autorizacion',
            'paciente_id' => 'required|exists:pacientes,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad_autorizada' => 'required|integer|min:1',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_emision',
            'lote_autorizado' => 'nullable|string|max:50',
            'fecha_vencimiento_lote' => 'nullable|date',
            'estado' => 'nullable|string|in:vigente,vencida,agotada,cancelada',
            'documento_soporte' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['cantidad_despachada'] = 0;
        $data['saldo_pendiente'] = $request->cantidad_autorizada;
        $data['estado'] = $request->estado ?? 'vigente';

        $autorizacion = AutorizacionInvima::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Autorización INVIMA creada exitosamente',
            'data' => $autorizacion->load(['paciente', 'producto'])
        ], 201);
    }

    public function show($id)
    {
        $autorizacion = AutorizacionInvima::with(['paciente', 'producto', 'detalleDespachos'])->find($id);

        if (!$autorizacion) {
            return response()->json(['success' => false, 'message' => 'Autorización no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $autorizacion], 200);
    }

    public function update(Request $request, $id)
    {
        $autorizacion = AutorizacionInvima::find($id);
        if (!$autorizacion) {
            return response()->json(['success' => false, 'message' => 'Autorización no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'cantidad_despachada' => 'sometimes|integer|min:0',
            'estado' => 'sometimes|string|in:vigente,vencida,agotada,cancelada',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($request->has('cantidad_despachada')) {
            $request->merge([
                'saldo_pendiente' => $autorizacion->cantidad_autorizada - $request->cantidad_despachada
            ]);
        }

        $autorizacion->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Autorización actualizada exitosamente',
            'data' => $autorizacion->load(['paciente', 'producto'])
        ], 200);
    }

    public function destroy($id)
    {
        $autorizacion = AutorizacionInvima::find($id);
        if (!$autorizacion) {
            return response()->json(['success' => false, 'message' => 'Autorización no encontrada'], 404);
        }

        if ($autorizacion->cantidad_despachada > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar, ya tiene despachos'], 400);
        }

        $autorizacion->update(['estado' => 'cancelada']);
        return response()->json(['success' => true, 'message' => 'Autorización cancelada'], 200);
    }

    public function proximasAVencer(Request $request)
    {
        $dias = $request->get('dias', 30);
        $fechaLimite = Carbon::now()->addDays($dias);

        $autorizaciones = AutorizacionInvima::with(['paciente', 'producto'])
            ->where('fecha_vencimiento', '<=', $fechaLimite)
            ->where('fecha_vencimiento', '>=', Carbon::now())
            ->where('estado', 'vigente')
            ->orderBy('fecha_vencimiento')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $autorizaciones,
            'total' => $autorizaciones->count()
        ], 200);
    }
}
