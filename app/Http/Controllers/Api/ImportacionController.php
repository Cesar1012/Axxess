<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Importacion;
use App\Models\DetalleImportacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ImportacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Importacion::with(['licenciaImportacion', 'laboratorio']);

        if ($request->has('licencia_importacion_id')) {
            $query->where('licencia_importacion_id', $request->licencia_importacion_id);
        }

        if ($request->has('laboratorio_id')) {
            $query->where('laboratorio_id', $request->laboratorio_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $perPage = $request->get('per_page', 15);
        $importaciones = $query->orderBy('fecha_importacion', 'desc')->paginate($perPage);

        return response()->json($importaciones, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_importacion' => 'required|string|max:50|unique:importaciones,numero_importacion',
            'licencia_importacion_id' => 'required|exists:licencias_importacion,id',
            'laboratorio_id' => 'required|exists:laboratorios,id',
            'fecha_importacion' => 'required|date',
            'numero_declaracion' => 'nullable|string|max:100',
            'numero_factura' => 'nullable|string|max:100',
            'numero_guia' => 'nullable|string|max:100',
            'valor_fob' => 'nullable|numeric|min:0',
            'valor_total' => 'required|numeric|min:0',
            'estado' => 'nullable|string|in:en_tramite,aprobada,recibida,cancelada',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.lote_id' => 'required|exists:lotes,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.subtotal' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $importacion = Importacion::create([
                'numero_importacion' => $request->numero_importacion,
                'licencia_importacion_id' => $request->licencia_importacion_id,
                'laboratorio_id' => $request->laboratorio_id,
                'fecha_importacion' => $request->fecha_importacion,
                'numero_declaracion' => $request->numero_declaracion,
                'numero_factura' => $request->numero_factura,
                'numero_guia' => $request->numero_guia,
                'valor_fob' => $request->valor_fob,
                'valor_total' => $request->valor_total,
                'estado' => $request->estado ?? 'en_tramite',
                'observaciones' => $request->observaciones
            ]);

            foreach ($request->detalles as $detalle) {
                DetalleImportacion::create([
                    'importacion_id' => $importacion->id,
                    'producto_id' => $detalle['producto_id'],
                    'lote_id' => $detalle['lote_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Importación creada exitosamente',
                'data' => $importacion->load(['licenciaImportacion', 'laboratorio', 'detalles'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al crear importación: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $importacion = Importacion::with([
            'licenciaImportacion.producto',
            'laboratorio',
            'detalles.producto',
            'detalles.lote'
        ])->find($id);

        if (!$importacion) {
            return response()->json(['success' => false, 'message' => 'Importación no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $importacion], 200);
    }

    public function update(Request $request, $id)
    {
        $importacion = Importacion::find($id);
        if (!$importacion) {
            return response()->json(['success' => false, 'message' => 'Importación no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'nullable|string|in:en_tramite,aprobada,recibida,cancelada',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $importacion->update($request->only(['estado', 'observaciones']));

        return response()->json([
            'success' => true,
            'message' => 'Importación actualizada',
            'data' => $importacion->load(['licenciaImportacion', 'laboratorio'])
        ], 200);
    }

    public function destroy($id)
    {
        $importacion = Importacion::find($id);
        if (!$importacion) {
            return response()->json(['success' => false, 'message' => 'Importación no encontrada'], 404);
        }

        if ($importacion->estado === 'recibida') {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una importación recibida'], 400);
        }

        $importacion->update(['estado' => 'cancelada']);
        return response()->json(['success' => true, 'message' => 'Importación cancelada'], 200);
    }
}
