<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Autenticación"},
     *     summary="Login de usuario",
     *     description="Autentica un usuario y genera un token de acceso Sanctum",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@glazgroup.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Admin2025!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre_completo", type="string", example="Administrador"),
     *                 @OA\Property(property="email", type="string", example="admin@glazgroup.com"),
     *                 @OA\Property(property="rol", type="string", example="administrador"),
     *                 @OA\Property(property="modulo_acceso", type="string", example="ambos")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|laravel_sanctum_token_here"),
     *             @OA\Property(property="message", type="string", example="Login exitoso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Las credenciales son incorrectas")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        // Validar datos de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Buscar usuario
        $usuario = Usuario::where('email', $request->email)->first();

        // Verificar si existe y la contraseña es correcta
        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Verificar si el usuario está activo
        if (!$usuario->activo) {
            throw ValidationException::withMessages([
                'email' => ['El usuario está inactivo. Contacte al administrador.'],
            ]);
        }

        // Actualizar último acceso
        $usuario->ultimo_acceso = now();
        $usuario->save();

        // Generar token
        $token = $usuario->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $usuario->id,
                'nombre_completo' => $usuario->nombre_completo,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'modulo_acceso' => $usuario->modulo_acceso,
                'activo' => $usuario->activo,
            ],
            'token' => $token,
            'message' => 'Login exitoso'
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Autenticación"},
     *     summary="Registro de nuevo usuario",
     *     description="Crea un nuevo usuario en el sistema",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre_completo","email","password","rol","modulo_acceso"},
     *             @OA\Property(property="nombre_completo", type="string", example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!"),
     *             @OA\Property(property="rol", type="string", enum={"administrador","control","consulta","ejecutivo_comercial"}, example="consulta"),
     *             @OA\Property(property="modulo_acceso", type="string", enum={"axxess_therapies","axxess_market","ambos"}, example="ambos"),
     *             @OA\Property(property="telefono", type="string", example="3001234567")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="message", type="string", example="Usuario registrado exitosamente")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function register(Request $request)
    {
        // Validar datos
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'rol' => 'required|in:administrador,control,consulta,ejecutivo_comercial',
            'modulo_acceso' => 'required|in:axxess_therapies,axxess_market,ambos',
            'telefono' => 'nullable|string|max:20',
        ]);

        // Crear usuario
        $usuario = Usuario::create([
            'nombre_completo' => $validated['nombre_completo'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'modulo_acceso' => $validated['modulo_acceso'],
            'telefono' => $validated['telefono'] ?? null,
            'activo' => true,
        ]);

        // Generar token
        $token = $usuario->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $usuario->id,
                'nombre_completo' => $usuario->nombre_completo,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'modulo_acceso' => $usuario->modulo_acceso,
            ],
            'token' => $token,
            'message' => 'Usuario registrado exitosamente'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     tags={"Autenticación"},
     *     summary="Obtener usuario autenticado",
     *     description="Retorna los datos del usuario actualmente autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Datos del usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre_completo", type="string", example="Administrador"),
     *                 @OA\Property(property="email", type="string", example="admin@glazgroup.com"),
     *                 @OA\Property(property="rol", type="string", example="administrador"),
     *                 @OA\Property(property="modulo_acceso", type="string", example="ambos")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Autenticación"},
     *     summary="Cerrar sesión",
     *     description="Revoca el token actual del usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Sesión cerrada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sesión cerrada exitosamente")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout-all",
     *     tags={"Autenticación"},
     *     summary="Cerrar todas las sesiones",
     *     description="Revoca todos los tokens del usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Todas las sesiones cerradas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Todas las sesiones han sido cerradas")
     *         )
     *     )
     * )
     */
    public function logoutAll(Request $request)
    {
        // Revocar todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Todas las sesiones han sido cerradas'
        ], 200);
    }
}
