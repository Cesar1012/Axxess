<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClienteMarket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteMarketController extends Controller
{
    public function index(Request $request)
    {
        $query = ClienteMarket::query();

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tipo_cliente')) {
            $query->where('tipo_cliente', $request->tipo_cliente);
        }

        $perPage = $request->get('per_page', 15);
        $clientes = $query->orderBy('razon_social')->paginate($perPage);

        return response()->json($clientes, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'razon_social' => 'required|string|max:150',
            'nit' => 'required|string|max:50|unique:clientes_market',
            'tipo_cliente' => 'nullable|string|max:30',
            'contacto_nombre' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion_entrega' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $cliente = ClienteMarket::create($request->all());

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'cliente' => $cliente
        ], 201);
    }

    public function show($id)
    {
        $cliente = ClienteMarket::with(['pedidos', 'ventas'])->find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente, 200);
    }

    public function update(Request $request, $id)
    {
        $cliente = ClienteMarket::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'razon_social' => 'sometimes|string|max:150',
            'nit' => 'sometimes|string|max:50|unique:clientes_market,nit,' . $id,
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $cliente->update($request->all());

        return response()->json([
            'message' => 'Cliente actualizado exitosamente',
            'cliente' => $cliente
        ], 200);
    }

    public function destroy($id)
    {
        $cliente = ClienteMarket::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $cliente->update(['estado' => 'inactivo']);

        return response()->json(['message' => 'Cliente desactivado exitosamente'], 200);
    }

    public function pedidos($id)
    {
        $cliente = ClienteMarket::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $pedidos = $cliente->pedidos()->with('detalles')->get();

        return response()->json($pedidos, 200);
    }

    public function estadoCartera($id)
    {
        $cliente = ClienteMarket::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $cuentasPorCobrar = $cliente->cuentasPorCobrar()
            ->where('estado', '!=', 'pagada')
            ->get();

        $totalDeuda = $cuentasPorCobrar->sum('saldo_pendiente');
        $facturasPendientes = $cuentasPorCobrar->count();

        return response()->json([
            'cliente' => $cliente->razon_social,
            'total_deuda' => $totalDeuda,
            'facturas_pendientes' => $facturasPendientes,
            'cartera_al_dia' => $cliente->cartera_al_dia,
            'limite_credito' => $cliente->limite_credito,
            'detalle' => $cuentasPorCobrar
        ], 200);
    }
}
