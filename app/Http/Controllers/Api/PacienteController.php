<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PacienteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/therapies/pacientes",
     *     tags={"Therapies - Pacientes"},
     *     summary="Listar pacientes",
     *     description="Retorna lista paginada de pacientes del módulo THERAPIES",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="activo",
     *         in="query",
     *         description="Filtrar por estado activo",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pacientes",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="codigo_paciente", type="string", example="PAC-001"),
     *                     @OA\Property(property="nombre_completo", type="string", example="Juan Pérez"),
     *                     @OA\Property(property="numero_documento", type="string", example="1012345678"),
     *                     @OA\Property(property="eps_asegurador", type="string", example="Nueva EPS"),
     *                     @OA\Property(property="telefono", type="string", example="+57-300-1234567")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Paciente::query();

        // Filtros opcionales
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        if ($request->has('eps_asegurador')) {
            $query->where('eps_asegurador', 'ILIKE', '%' . $request->eps_asegurador . '%');
        }

        if ($request->has('zona_cobertura')) {
            $query->where('zona_cobertura', $request->zona_cobertura);
        }

        $perPage = $request->get('per_page', 15);
        $pacientes = $query->orderBy('nombre_completo')->paginate($perPage);

        return response()->json($pacientes, 200);
    }

    /**
     * Crear un nuevo paciente
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo_paciente' => 'required|string|max:50|unique:pacientes',
            'nombre_completo' => 'required|string|max:150',
            'tipo_documento' => 'required|string|max:20',
            'numero_documento' => 'required|string|max:50|unique:pacientes',
            'eps_asegurador' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion_entrega' => 'nullable|string',
            'zona_cobertura' => 'nullable|string|max:100',
            'requiere_cita_entrega' => 'nullable|boolean',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $paciente = Paciente::create($request->all());

        return response()->json([
            'message' => 'Paciente creado exitosamente',
            'paciente' => $paciente
        ], 201);
    }

    /**
     * Mostrar un paciente específico
     */
    public function show($id)
    {
        $paciente = Paciente::with(['autorizaciones', 'entregas', 'despachos'])->find($id);

        if (!$paciente) {
            return response()->json([
                'message' => 'Paciente no encontrado'
            ], 404);
        }

        return response()->json($paciente, 200);
    }

    /**
     * Actualizar un paciente
     */
    public function update(Request $request, $id)
    {
        $paciente = Paciente::find($id);

        if (!$paciente) {
            return response()->json([
                'message' => 'Paciente no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo_paciente' => 'sometimes|string|max:50|unique:pacientes,codigo_paciente,' . $id,
            'nombre_completo' => 'sometimes|string|max:150',
            'tipo_documento' => 'sometimes|string|max:20',
            'numero_documento' => 'sometimes|string|max:50|unique:pacientes,numero_documento,' . $id,
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $paciente->update($request->all());

        return response()->json([
            'message' => 'Paciente actualizado exitosamente',
            'paciente' => $paciente
        ], 200);
    }

    /**
     * Eliminar (desactivar) un paciente
     */
    public function destroy($id)
    {
        $paciente = Paciente::find($id);

        if (!$paciente) {
            return response()->json([
                'message' => 'Paciente no encontrado'
            ], 404);
        }

        $paciente->update(['activo' => false]);

        return response()->json([
            'message' => 'Paciente desactivado exitosamente'
        ], 200);
    }

    public function buscar($termino)
    {
        $pacientes = Paciente::where('nombre_completo', 'ILIKE', "%{$termino}%")
            ->orWhere('numero_documento', 'ILIKE', "%{$termino}%")
            ->orWhere('codigo_paciente', 'ILIKE', "%{$termino}%")
            ->where('activo', true)
            ->limit(20)
            ->get();

        return response()->json($pacientes, 200);
    }
}
