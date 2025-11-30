<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Categoria::query();

        if ($request->has('tipo_modulo')) {
            $query->where('tipo_modulo', $request->tipo_modulo);
        }

        if ($request->has('activa')) {
            $query->where('activa', $request->activa);
        }

        $categorias = $query->orderBy('nombre')->get();
        return response()->json($categorias, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'tipo_modulo' => 'required|string|in:therapies,market,ambos',
            'descripcion' => 'nullable|string',
            'activa' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $categoria = Categoria::create($request->all());
        return response()->json(['success' => true, 'message' => 'Categoría creada', 'data' => $categoria], 201);
    }

    public function show($id)
    {
        $categoria = Categoria::with(['productos'])->find($id);
        if (!$categoria) {
            return response()->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
        }
        return response()->json(['success' => true, 'data' => $categoria], 200);
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:100',
            'tipo_modulo' => 'sometimes|required|string|in:therapies,market,ambos',
            'descripcion' => 'nullable|string',
            'activa' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $categoria->update($request->all());
        return response()->json(['success' => true, 'message' => 'Categoría actualizada', 'data' => $categoria], 200);
    }

    public function destroy($id)
    {
        $categoria = Categoria::find($id);
        if (!$categoria) {
            return response()->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
        }

        if ($categoria->productos()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar, tiene productos asociados'], 400);
        }

        $categoria->update(['activa' => false]);
        return response()->json(['success' => true, 'message' => 'Categoría desactivada'], 200);
    }
}
