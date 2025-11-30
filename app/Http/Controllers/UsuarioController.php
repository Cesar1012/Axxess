<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Listar usuarios con filtros y paginación
     */
    public function index(Request $request)
    {
        $query = Usuario::query();

        // Búsqueda por nombre o email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_completo', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->has('rol') && $request->rol !== '') {
            $query->where('rol', $request->rol);
        }

        // Filtro por módulo
        if ($request->has('modulo_acceso') && $request->modulo_acceso !== '') {
            $query->where('modulo_acceso', $request->modulo_acceso);
        }

        // Filtro por activo
        if ($request->has('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }

        // Ordenar por fecha de creación desc
        $query->orderBy('fecha_creacion', 'desc');

        // Paginación
        $perPage = $request->get('per_page', 10);
        $usuarios = $query->paginate($perPage);

        return response()->json($usuarios);
    }

    /**
     * Crear nuevo usuario
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email|max:100',
            'password' => 'required|string|min:6',
            'rol' => ['required', Rule::in(['administrador', 'control', 'consulta', 'ejecutivo_comercial'])],
            'modulo_acceso' => ['required', Rule::in(['axxess_therapies', 'axxess_market', 'ambos'])],
            'telefono' => 'nullable|string|max:20',
            'activo' => 'boolean'
        ]);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);
        $validated['fecha_creacion'] = now();

        $usuario = Usuario::create($validated);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario
        ], 201);
    }

    /**
     * Mostrar un usuario específico
     */
    public function show($id)
    {
        $usuario = Usuario::findOrFail($id);
        return response()->json(['data' => $usuario]);
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $validated = $request->validate([
            'nombre_completo' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', 'max:100', Rule::unique('usuarios')->ignore($id)],
            'password' => 'sometimes|nullable|string|min:6',
            'rol' => ['sometimes', 'required', Rule::in(['administrador', 'control', 'consulta', 'ejecutivo_comercial'])],
            'modulo_acceso' => ['sometimes', 'required', Rule::in(['axxess_therapies', 'axxess_market', 'ambos'])],
            'telefono' => 'nullable|string|max:20',
            'activo' => 'sometimes|boolean'
        ]);

        // Solo hashear password si fue enviado
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'data' => $usuario->fresh()
        ]);
    }

    /**
     * Eliminar usuario
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        
        // Prevenir eliminar el último administrador
        if ($usuario->rol === 'administrador') {
            $adminCount = Usuario::where('rol', 'administrador')->where('activo', true)->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'No se puede eliminar el único administrador del sistema'
                ], 422);
            }
        }

        $usuario->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleStatus($id)
    {
        $usuario = Usuario::findOrFail($id);
        
        // Prevenir desactivar el último administrador
        if ($usuario->rol === 'administrador' && $usuario->activo) {
            $adminCount = Usuario::where('rol', 'administrador')->where('activo', true)->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'No se puede desactivar el único administrador activo del sistema'
                ], 422);
            }
        }

        $usuario->activo = !$usuario->activo;
        $usuario->save();

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data' => $usuario
        ]);
    }

    /**
     * Estadísticas de usuarios
     */
    public function stats()
    {
        $stats = [
            'total' => Usuario::count(),
            'activos' => Usuario::where('activo', true)->count(),
            'inactivos' => Usuario::where('activo', false)->count(),
            'por_rol' => [
                'administrador' => Usuario::where('rol', 'administrador')->count(),
                'control' => Usuario::where('rol', 'control')->count(),
                'consulta' => Usuario::where('rol', 'consulta')->count(),
                'ejecutivo_comercial' => Usuario::where('rol', 'ejecutivo_comercial')->count(),
            ],
            'por_modulo' => [
                'therapies' => Usuario::where('modulo_acceso', 'axxess_therapies')->count(),
                'market' => Usuario::where('modulo_acceso', 'axxess_market')->count(),
                'ambos' => Usuario::where('modulo_acceso', 'ambos')->count(),
            ]
        ];

        return response()->json(['data' => $stats]);
    }
}
