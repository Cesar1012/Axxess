<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recaudo;
use App\Models\CuentaPorCobrar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RecaudoController extends Controller
{
    public function index(Request $request)
    {
        $query = Recaudo::with(['cuentaCobrar', 'vendedor']);

        if ($request->has('cuenta_cobrar_id')) {
            $query->where('cuenta_cobrar_id', $request->cuenta_cobrar_id);
        }

        if ($request->has('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        if ($request->has('forma_pago')) {
            $query->where('forma_pago', $request->forma_pago);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_recaudo', '>=', $request->fecha_desde);
        }

        $perPage = $request->get('per_page', 15);
        $recaudos = $query->orderBy('fecha_recaudo', 'desc')->paginate($perPage);
        return response()->json($recaudos, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cuenta_cobrar_id' => 'required|exists:cuentas_por_cobrar,id',
            'numero_factura' => 'required|string|max:50',
            'vendedor_id' => 'nullable|exists:vendedores,id',
            'fecha_recaudo' => 'required|date',
            'valor_recaudado' => 'required|numeric|min:0',
            'forma_pago' => 'nullable|string|max:20',
            'numero_transaccion' => 'nullable|string|max:100',
            'documento_soporte' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $cuenta = CuentaPorCobrar::find($request->cuenta_cobrar_id);

            if (!$cuenta) {
                return response()->json(['success' => false, 'message' => 'Cuenta por cobrar no encontrada'], 404);
            }

            // Validar que no se exceda el saldo pendiente
            if ($request->valor_recaudado > $cuenta->saldo_pendiente) {
                return response()->json([
                    'success' => false, 
                    'message' => 'El valor recaudado excede el saldo pendiente'
                ], 400);
            }

            $recaudo = Recaudo::create($request->all());

            // Actualizar cuenta por cobrar
            $nuevoValorPagado = $cuenta->valor_pagado + $request->valor_recaudado;
            $nuevoSaldo = $cuenta->valor_total - $nuevoValorPagado;
            $estado = $nuevoSaldo <= 0 ? 'pagada' : $cuenta->estado;

            $cuenta->update([
                'valor_pagado' => $nuevoValorPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => $estado
            ]);

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Recaudo registrado exitosamente',
                'data' => $recaudo->load(['cuentaCobrar', 'vendedor'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $recaudo = Recaudo::with(['cuentaCobrar.clienteMarket', 'vendedor'])->find($id);
        if (!$recaudo) {
            return response()->json(['success' => false, 'message' => 'Recaudo no encontrado'], 404);
        }
        return response()->json(['success' => true, 'data' => $recaudo], 200);
    }

    public function update(Request $request, $id)
    {
        $recaudo = Recaudo::find($id);
        if (!$recaudo) {
            return response()->json(['success' => false, 'message' => 'Recaudo no encontrado'], 404);
        }

        $recaudo->update($request->only(['observaciones', 'documento_soporte']));
        return response()->json(['success' => true, 'message' => 'Recaudo actualizado', 'data' => $recaudo], 200);
    }

    public function destroy($id)
    {
        $recaudo = Recaudo::find($id);
        if (!$recaudo) {
            return response()->json(['success' => false, 'message' => 'Recaudo no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $cuenta = $recaudo->cuentaCobrar;
            
            // Revertir el pago en la cuenta
            $cuenta->update([
                'valor_pagado' => $cuenta->valor_pagado - $recaudo->valor_recaudado,
                'saldo_pendiente' => $cuenta->saldo_pendiente + $recaudo->valor_recaudado,
                'estado' => 'vigente'
            ]);

            $recaudo->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Recaudo eliminado'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function porVendedor($vendedorId, Request $request)
    {
        $query = Recaudo::with(['cuentaCobrar'])
            ->where('vendedor_id', $vendedorId);

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_recaudo', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_recaudo', '<=', $request->fecha_hasta);
        }

        $recaudos = $query->orderBy('fecha_recaudo', 'desc')->get();
        $totalRecaudado = $recaudos->sum('valor_recaudado');

        return response()->json([
            'success' => true, 
            'data' => $recaudos, 
            'total_recaudado' => $totalRecaudado,
            'cantidad_recaudos' => $recaudos->count()
        ], 200);
    }
}
