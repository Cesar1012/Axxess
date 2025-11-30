<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Despacho;
use App\Models\DetalleDespacho;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DespachoController extends Controller
{
    public function index(Request $request)
    {
        $query = Despacho::with(['pedido', 'paciente', 'clienteMarket', 'usuarioPreparo']);

        if ($request->has('pedido_id')) {
            $query->where('pedido_id', $request->pedido_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_despacho', '>=', $request->fecha_desde);
        }

        $perPage = $request->get('per_page', 15);
        $despachos = $query->orderBy('fecha_despacho', 'desc')->paginate($perPage);
        return response()->json($despachos, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_remision' => 'required|string|max:50|unique:despachos,numero_remision',
            'numero_guia' => 'nullable|string|max:50',
            'pedido_id' => 'required|exists:pedidos,id',
            'direccion_entrega' => 'required|string',
            'transportista' => 'nullable|string|max:100',
            'vehiculo_placa' => 'nullable|string|max:20',
            'hora_cargue' => 'nullable',
            'requiere_cita' => 'boolean',
            'soporte_entrega' => 'boolean',
            'estado' => 'nullable|string|in:preparado,en_ruta,entregado,cancelado',
            'usuario_preparo_id' => 'required|exists:usuarios,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.lote_id' => 'required|exists:lotes,id',
            'detalles.*.cantidad' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $despacho = Despacho::create([
                'numero_remision' => $request->numero_remision,
                'numero_guia' => $request->numero_guia,
                'pedido_id' => $request->pedido_id,
                'direccion_entrega' => $request->direccion_entrega,
                'transportista' => $request->transportista,
                'vehiculo_placa' => $request->vehiculo_placa,
                'hora_cargue' => $request->hora_cargue,
                'requiere_cita' => $request->requiere_cita ?? false,
                'soporte_entrega' => $request->soporte_entrega ?? false,
                'estado' => $request->estado ?? 'preparado',
                'usuario_preparo_id' => $request->usuario_preparo_id,
                'observaciones' => $request->observaciones
            ]);

            foreach ($request->detalles as $detalle) {
                DetalleDespacho::create([
                    'despacho_id' => $despacho->id,
                    'producto_id' => $detalle['producto_id'],
                    'lote_id' => $detalle['lote_id'],
                    'cantidad' => $detalle['cantidad']
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Despacho creado exitosamente',
                'data' => $despacho->load(['pedido', 'detalles'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $despacho = Despacho::with(['pedido', 'paciente', 'clienteMarket', 'detalles.producto', 'detalles.lote'])->find($id);
        if (!$despacho) {
            return response()->json(['success' => false, 'message' => 'Despacho no encontrado'], 404);
        }
        return response()->json(['success' => true, 'data' => $despacho], 200);
    }

    public function update(Request $request, $id)
    {
        $despacho = Despacho::find($id);
        if (!$despacho) {
            return response()->json(['success' => false, 'message' => 'Despacho no encontrado'], 404);
        }

        $despacho->update($request->only(['estado', 'fecha_entrega', 'firma_recibido', 'observaciones']));
        return response()->json(['success' => true, 'message' => 'Despacho actualizado', 'data' => $despacho], 200);
    }

    public function destroy($id)
    {
        $despacho = Despacho::find($id);
        if (!$despacho) {
            return response()->json(['success' => false, 'message' => 'Despacho no encontrado'], 404);
        }

        if ($despacho->estado === 'entregado') {
            return response()->json(['success' => false, 'message' => 'No se puede cancelar un despacho entregado'], 400);
        }

        $despacho->update(['estado' => 'cancelado']);
        return response()->json(['success' => true, 'message' => 'Despacho cancelado'], 200);
    }
}
