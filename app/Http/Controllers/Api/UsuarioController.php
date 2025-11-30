<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @group Usuarios
 * 
 * API para gestionar usuarios del sistema
 */
class UsuarioController extends Controller
{
    /**
     * Listar todos los usuarios
     * 
     * @queryParam activo boolean Filtrar por estado. Example: 1
     * @queryParam rol string Filtrar por rol. Example: administrador
     * @queryParam modulo_acceso string Filtrar por módulo. Example: ambos
     */
    public function index(Request $request)
    {
        $query = Usuario::query();

        // Filtros opcionales
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        if ($request->has('rol')) {
            $query->where('rol', $request->rol);
        }

        if ($request->has('modulo_acceso')) {
            $query->where('modulo_acceso', $request->modulo_acceso);
        }

        $perPage = $request->get('per_page', 15);
        $usuarios = $query->orderBy('nombre_completo')->paginate($perPage);

        return response()->json($usuarios, 200);
    }

    /**
     * Crear un nuevo usuario
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'rol' => 'required|in:administrador,control,consulta,ejecutivo_comercial',
            'modulo_acceso' => 'required|in:axxess_therapies,axxess_market,ambos',
            'telefono' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::create([
            'nombre_completo' => $request->nombre_completo,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'modulo_acceso' => $request->modulo_acceso,
            'telefono' => $request->telefono,
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ], 201);
    }

    /**
     * Mostrar un usuario específico
     */
    public function show($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json($usuario, 200);
    }

    /**
     * Actualizar un usuario
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_completo' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:usuarios,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'rol' => 'sometimes|in:administrador,control,consulta,ejecutivo_comercial',
            'modulo_acceso' => 'sometimes|in:axxess_therapies,axxess_market,ambos',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('password');
        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'usuario' => $usuario
        ], 200);
    }

    /**
     * Eliminar (desactivar) un usuario
     */
    public function destroy($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Soft delete - solo desactivar
        $usuario->update(['activo' => false]);

        return response()->json([
            'message' => 'Usuario desactivado exitosamente'
        ], 200);
    }
}
