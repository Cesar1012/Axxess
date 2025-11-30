<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReporteGenerado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReporteGeneradoController extends Controller
{
    public function index(Request $request)
    {
        $query = ReporteGenerado::with(['usuario']);

        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->has('tipo_reporte')) {
            $query->where('tipo_reporte', 'ILIKE', "%{$request->tipo_reporte}%");
        }

        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->has('enviado_email')) {
            $query->where('enviado_email', $request->enviado_email);
        }

        $perPage = $request->get('per_page', 15);
        $reportes = $query->orderBy('fecha_generacion', 'desc')->paginate($perPage);
        return response()->json($reportes, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_reporte' => 'required|string|max:100',
            'usuario_id' => 'required|exists:usuarios,id',
            'modulo' => 'nullable|string|max:20',
            'parametros' => 'nullable|json',
            'archivo_resultado' => 'nullable|string|max:255',
            'enviado_email' => 'boolean',
            'email_destinatario' => 'nullable|email|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $reporte = ReporteGenerado::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Reporte registrado exitosamente',
            'data' => $reporte
        ], 201);
    }

    public function show($id)
    {
        $reporte = ReporteGenerado::with(['usuario'])->find($id);
        if (!$reporte) {
            return response()->json(['success' => false, 'message' => 'Reporte no encontrado'], 404);
        }
        return response()->json(['success' => true, 'data' => $reporte], 200);
    }

    public function update(Request $request, $id)
    {
        $reporte = ReporteGenerado::find($id);
        if (!$reporte) {
            return response()->json(['success' => false, 'message' => 'Reporte no encontrado'], 404);
        }

        $reporte->update($request->only(['enviado_email', 'email_destinatario']));
        return response()->json(['success' => true, 'message' => 'Reporte actualizado', 'data' => $reporte], 200);
    }

    public function destroy($id)
    {
        $reporte = ReporteGenerado::find($id);
        if (!$reporte) {
            return response()->json(['success' => false, 'message' => 'Reporte no encontrado'], 404);
        }

        $reporte->delete();
        return response()->json(['success' => true, 'message' => 'Reporte eliminado'], 200);
    }

    public function porUsuario($usuarioId, Request $request)
    {
        $query = ReporteGenerado::where('usuario_id', $usuarioId);

        if ($request->has('tipo_reporte')) {
            $query->where('tipo_reporte', 'ILIKE', "%{$request->tipo_reporte}%");
        }

        $reportes = $query->orderBy('fecha_generacion', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $reportes,
            'total' => $reportes->count()
        ], 200);
    }

    public function resumenPorTipo(Request $request)
    {
        $query = ReporteGenerado::query();

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_generacion', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_generacion', '<=', $request->fecha_hasta);
        }

        $resumen = $query->selectRaw('tipo_reporte, COUNT(*) as total')
            ->groupBy('tipo_reporte')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $resumen], 200);
    }
}
