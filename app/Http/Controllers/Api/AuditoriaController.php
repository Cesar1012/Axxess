<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Auditoria::with(['usuario']);

        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->has('tabla_afectada')) {
            $query->where('tabla_afectada', $request->tabla_afectada);
        }

        if ($request->has('accion')) {
            $query->where('accion', 'ILIKE', "%{$request->accion}%");
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }

        $perPage = $request->get('per_page', 20);
        $auditorias = $query->orderBy('fecha_hora', 'desc')->paginate($perPage);
        return response()->json($auditorias, 200);
    }

    public function show($id)
    {
        $auditoria = Auditoria::with(['usuario'])->find($id);
        if (!$auditoria) {
            return response()->json(['success' => false, 'message' => 'Registro de auditoría no encontrado'], 404);
        }
        return response()->json(['success' => true, 'data' => $auditoria], 200);
    }

    public function porUsuario($usuarioId, Request $request)
    {
        $query = Auditoria::where('usuario_id', $usuarioId);

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }

        $auditorias = $query->orderBy('fecha_hora', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $auditorias,
            'total_acciones' => $auditorias->count()
        ], 200);
    }

    public function porModulo($modulo, Request $request)
    {
        $query = Auditoria::with(['usuario'])->where('modulo', $modulo);

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }

        $auditorias = $query->orderBy('fecha_hora', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $auditorias,
            'total' => $auditorias->count()
        ], 200);
    }

    public function resumenAcciones(Request $request)
    {
        $query = Auditoria::query();

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }

        $resumen = $query->selectRaw('accion, COUNT(*) as total')
            ->groupBy('accion')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $resumen], 200);
    }
}
