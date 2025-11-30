<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlertaController extends Controller
{
    public function index(Request $request)
    {
        $query = Alerta::query();

        if ($request->has('tipo_alerta')) {
            $query->where('tipo_alerta', $request->tipo_alerta);
        }

        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->has('leida')) {
            $query->where('leida', $request->leida);
        }

        if ($request->has('resuelta')) {
            $query->where('resuelta', $request->resuelta);
        }

        if ($request->has('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        $perPage = $request->get('per_page', 20);
        $alertas = $query->orderBy('fecha_creacion', 'desc')->paginate($perPage);
        return response()->json($alertas, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_alerta' => 'required|string|max:50',
            'modulo' => 'nullable|string|max:20',
            'mensaje' => 'required|string',
            'prioridad' => 'nullable|string|in:baja,media,alta,critica',
            'fecha_vencimiento_alerta' => 'nullable|date',
            'enviar_email' => 'boolean',
            'destinatarios_email' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $alerta = Alerta::create($request->all());
        return response()->json(['success' => true, 'message' => 'Alerta creada', 'data' => $alerta], 201);
    }

    public function show($id)
    {
        $alerta = Alerta::find($id);
        if (!$alerta) {
            return response()->json(['success' => false, 'message' => 'Alerta no encontrada'], 404);
        }
        return response()->json(['success' => true, 'data' => $alerta], 200);
    }

    public function update(Request $request, $id)
    {
        $alerta = Alerta::find($id);
        if (!$alerta) {
            return response()->json(['success' => false, 'message' => 'Alerta no encontrada'], 404);
        }

        $alerta->update($request->only(['leida', 'resuelta', 'observaciones']));
        return response()->json(['success' => true, 'message' => 'Alerta actualizada', 'data' => $alerta], 200);
    }

    public function destroy($id)
    {
        $alerta = Alerta::find($id);
        if (!$alerta) {
            return response()->json(['success' => false, 'message' => 'Alerta no encontrada'], 404);
        }

        $alerta->delete();
        return response()->json(['success' => true, 'message' => 'Alerta eliminada'], 200);
    }

    public function marcarLeida($id)
    {
        $alerta = Alerta::find($id);
        if (!$alerta) {
            return response()->json(['success' => false, 'message' => 'Alerta no encontrada'], 404);
        }

        $alerta->update(['leida' => true]);
        return response()->json(['success' => true, 'message' => 'Alerta marcada como leída'], 200);
    }

    public function marcarResuelta($id)
    {
        $alerta = Alerta::find($id);
        if (!$alerta) {
            return response()->json(['success' => false, 'message' => 'Alerta no encontrada'], 404);
        }

        $alerta->update(['resuelta' => true]);
        return response()->json(['success' => true, 'message' => 'Alerta marcada como resuelta'], 200);
    }

    public function noLeidas()
    {
        $alertas = Alerta::where('leida', false)
            ->orderBy('prioridad', 'desc')
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $alertas, 'total' => $alertas->count()], 200);
    }

    public function porPrioridad($prioridad)
    {
        $alertas = Alerta::where('prioridad', $prioridad)
            ->where('resuelta', false)
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $alertas, 'total' => $alertas->count()], 200);
    }
}
