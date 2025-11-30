<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CuentaPorCobrar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CuentaPorCobrarController extends Controller
{
    public function index(Request $request)
    {
        $query = CuentaPorCobrar::with(['venta', 'clienteMarket']);

        if ($request->has('cliente_market_id')) {
            $query->where('cliente_market_id', $request->cliente_market_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('vencidas')) {
            $query->where('fecha_vencimiento', '<', Carbon::now())
                  ->where('estado', 'vigente');
        }

        $perPage = $request->get('per_page', 15);
        $cuentas = $query->orderBy('fecha_vencimiento')->paginate($perPage);
        return response()->json($cuentas, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'venta_id' => 'required|exists:ventas,id',
            'numero_factura' => 'required|string|max:50',
            'cliente_market_id' => 'required|exists:clientes_market,id',
            'valor_total' => 'required|numeric|min:0',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'required|date|after:fecha_emision'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['valor_pagado'] = 0;
        $data['saldo_pendiente'] = $request->valor_total;
        $data['dias_mora'] = 0;
        $data['estado'] = 'vigente';

        $cuenta = CuentaPorCobrar::create($data);
        return response()->json(['success' => true, 'message' => 'Cuenta por cobrar creada', 'data' => $cuenta], 201);
    }

    public function show($id)
    {
        $cuenta = CuentaPorCobrar::with(['venta', 'clienteMarket', 'recaudos'])->find($id);
        if (!$cuenta) {
            return response()->json(['success' => false, 'message' => 'Cuenta no encontrada'], 404);
        }
        return response()->json(['success' => true, 'data' => $cuenta], 200);
    }

    public function update(Request $request, $id)
    {
        $cuenta = CuentaPorCobrar::find($id);
        if (!$cuenta) {
            return response()->json(['success' => false, 'message' => 'Cuenta no encontrada'], 404);
        }

        // Calcular días de mora
        if ($request->has('valor_pagado')) {
            $saldoPendiente = $cuenta->valor_total - $request->valor_pagado;
            $diasMora = $saldoPendiente > 0 && Carbon::now()->gt($cuenta->fecha_vencimiento) 
                ? Carbon::now()->diffInDays($cuenta->fecha_vencimiento) 
                : 0;

            $estado = $saldoPendiente <= 0 ? 'pagada' : ($diasMora > 0 ? 'vencida' : 'vigente');

            $cuenta->update([
                'valor_pagado' => $request->valor_pagado,
                'saldo_pendiente' => $saldoPendiente,
                'dias_mora' => $diasMora,
                'estado' => $estado
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Cuenta actualizada', 'data' => $cuenta], 200);
    }

    public function destroy($id)
    {
        $cuenta = CuentaPorCobrar::find($id);
        if (!$cuenta) {
            return response()->json(['success' => false, 'message' => 'Cuenta no encontrada'], 404);
        }

        if ($cuenta->valor_pagado > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede cancelar, ya tiene pagos'], 400);
        }

        $cuenta->update(['estado' => 'cancelada']);
        return response()->json(['success' => true, 'message' => 'Cuenta cancelada'], 200);
    }

    public function vencidas()
    {
        $cuentas = CuentaPorCobrar::with(['clienteMarket', 'venta'])
            ->where('fecha_vencimiento', '<', Carbon::now())
            ->where('saldo_pendiente', '>', 0)
            ->where('estado', '!=', 'pagada')
            ->orderBy('fecha_vencimiento')
            ->get();

        return response()->json(['success' => true, 'data' => $cuentas, 'total' => $cuentas->count()], 200);
    }

    public function porVencer(Request $request)
    {
        $dias = $request->get('dias', 15);
        $fechaLimite = Carbon::now()->addDays($dias);

        $cuentas = CuentaPorCobrar::with(['clienteMarket', 'venta'])
            ->where('fecha_vencimiento', '<=', $fechaLimite)
            ->where('fecha_vencimiento', '>=', Carbon::now())
            ->where('estado', 'vigente')
            ->orderBy('fecha_vencimiento')
            ->get();

        return response()->json(['success' => true, 'data' => $cuentas, 'total' => $cuentas->count()], 200);
    }
}
