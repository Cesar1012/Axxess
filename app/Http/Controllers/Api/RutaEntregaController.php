<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RutaEntrega;
use App\Models\DespachoRuta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RutaEntregaController extends Controller
{
    public function index(Request $request)
    {
        $query = RutaEntrega::query();

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fecha_ruta')) {
            $query->whereDate('fecha_ruta', $request->fecha_ruta);
        }

        if ($request->has('zona')) {
            $query->where('zona', 'ILIKE', "%{$request->zona}%");
        }

        $perPage = $request->get('per_page', 15);
        $rutas = $query->orderBy('fecha_ruta', 'desc')->paginate($perPage);
        return response()->json($rutas, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_ruta' => 'required|string|max:100',
            'fecha_ruta' => 'required|date',
            'zona' => 'nullable|string|max:100',
            'transportista' => 'nullable|string|max:100',
            'vehiculo_placa' => 'nullable|string|max:20',
            'hora_salida' => 'nullable',
            'estado' => 'nullable|string|in:planificada,en_curso,completada,cancelada',
            'observaciones' => 'nullable|string',
            'despachos' => 'nullable|array',
            'despachos.*.despacho_id' => 'required|exists:despachos,id',
            'despachos.*.orden_entrega' => 'required|integer|min:1',
            'despachos.*.hora_estimada' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $ruta = RutaEntrega::create([
                'nombre_ruta' => $request->nombre_ruta,
                'fecha_ruta' => $request->fecha_ruta,
                'zona' => $request->zona,
                'transportista' => $request->transportista,
                'vehiculo_placa' => $request->vehiculo_placa,
                'hora_salida' => $request->hora_salida,
                'estado' => $request->estado ?? 'planificada',
                'observaciones' => $request->observaciones
            ]);

            if ($request->has('despachos')) {
                foreach ($request->despachos as $despacho) {
                    DespachoRuta::create([
                        'ruta_id' => $ruta->id,
                        'despacho_id' => $despacho['despacho_id'],
                        'orden_entrega' => $despacho['orden_entrega'],
                        'hora_estimada' => $despacho['hora_estimada'] ?? null
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ruta creada exitosamente',
                'data' => $ruta->load('despachosRuta.despacho')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $ruta = RutaEntrega::with(['despachosRuta.despacho.pedido'])->find($id);
        if (!$ruta) {
            return response()->json(['success' => false, 'message' => 'Ruta no encontrada'], 404);
        }
        return response()->json(['success' => true, 'data' => $ruta], 200);
    }

    public function update(Request $request, $id)
    {
        $ruta = RutaEntrega::find($id);
        if (!$ruta) {
            return response()->json(['success' => false, 'message' => 'Ruta no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'nullable|string|in:planificada,en_curso,completada,cancelada',
            'hora_salida' => 'nullable',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $ruta->update($request->all());
        return response()->json(['success' => true, 'message' => 'Ruta actualizada', 'data' => $ruta], 200);
    }

    public function destroy($id)
    {
        $ruta = RutaEntrega::find($id);
        if (!$ruta) {
            return response()->json(['success' => false, 'message' => 'Ruta no encontrada'], 404);
        }

        if ($ruta->estado === 'completada') {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una ruta completada'], 400);
        }

        $ruta->delete();
        return response()->json(['success' => true, 'message' => 'Ruta eliminada'], 200);
    }

    public function agregarDespacho(Request $request, $id)
    {
        $ruta = RutaEntrega::find($id);
        if (!$ruta) {
            return response()->json(['success' => false, 'message' => 'Ruta no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'despacho_id' => 'required|exists:despachos,id',
            'orden_entrega' => 'required|integer|min:1',
            'hora_estimada' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DespachoRuta::create([
            'ruta_id' => $id,
            'despacho_id' => $request->despacho_id,
            'orden_entrega' => $request->orden_entrega,
            'hora_estimada' => $request->hora_estimada
        ]);

        return response()->json(['success' => true, 'message' => 'Despacho agregado a la ruta'], 200);
    }
}
