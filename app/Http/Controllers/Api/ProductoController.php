<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Exports\ProductosExport;
use App\Imports\ProductosImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/productos",
     *     tags={"Productos"},
     *     summary="Listar productos",
     *     description="Retorna lista paginada de productos con sus categorías y lotes",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Productos por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="modulo",
     *         in="query",
     *         description="Filtrar por módulo",
     *         required=false,
     *         @OA\Schema(type="string", enum={"therapies", "market"}, example="market")
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
     *         description="Lista de productos",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="codigo_producto", type="string", example="PROD-001"),
     *                     @OA\Property(property="nombre", type="string", example="Eculizumab 300mg"),
     *                     @OA\Property(property="modulo", type="string", example="therapies"),
     *                     @OA\Property(property="precio_compra", type="number", example=15000000.00),
     *                     @OA\Property(property="precio_venta", type="number", example=18000000.00),
     *                     @OA\Property(property="stock_actual", type="integer", example=10),
     *                     @OA\Property(property="activo", type="boolean", example=true)
     *                 )
     *             ),
     *             @OA\Property(property="total", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'lotes']);

        // Filtros opcionales
        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Paginación
        $perPage = $request->get('per_page', 15);
        $productos = $query->orderBy('nombre')->paginate($perPage);

        return response()->json($productos, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/productos",
     *     tags={"Productos"},
     *     summary="Crear producto",
     *     description="Crea un nuevo producto en el sistema",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"codigo_producto","nombre","modulo","precio_compra","precio_venta","unidad_medida"},
     *             @OA\Property(property="codigo_producto", type="string", example="PROD-999"),
     *             @OA\Property(property="nombre", type="string", example="Producto Demo"),
     *             @OA\Property(property="descripcion", type="string", example="Descripción del producto"),
     *             @OA\Property(property="categoria_id", type="integer", example=1),
     *             @OA\Property(property="modulo", type="string", enum={"therapies","market"}, example="market"),
     *             @OA\Property(property="precio_compra", type="number", example=50000),
     *             @OA\Property(property="precio_venta", type="number", example=75000),
     *             @OA\Property(property="stock_minimo", type="integer", example=10),
     *             @OA\Property(property="stock_actual", type="integer", example=50),
     *             @OA\Property(property="unidad_medida", type="string", example="unidad"),
     *             @OA\Property(property="activo", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto creado exitosamente"),
     *             @OA\Property(property="producto", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo_producto' => 'required|string|max:50|unique:productos',
            'nombre' => 'required|string|max:200',
            'categoria_id' => 'nullable|exists:categorias,id',
            'modulo' => 'required|in:therapies,market',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo' => 'nullable|integer|min:0',
            'codigo_barras' => 'nullable|string|max:50',
            'molecula' => 'nullable|string|max:100',
            'requiere_cadena_frio' => 'nullable|boolean',
            'requiere_acondicionamiento' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $producto = Producto::create($request->all());

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'producto' => $producto
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/productos/{id}",
     *     tags={"Productos"},
     *     summary="Obtener producto",
     *     description="Retorna un producto específico por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Producto encontrado"),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'lotes', 'lotes.bodega'])->find($id);

        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json($producto, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/productos/{id}",
     *     tags={"Productos"},
     *     summary="Actualizar producto",
     *     description="Actualiza un producto existente",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Producto Actualizado"),
     *             @OA\Property(property="precio_venta", type="number", example=80000)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Producto actualizado"),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo_producto' => 'sometimes|string|max:50|unique:productos,codigo_producto,' . $id,
            'nombre' => 'sometimes|string|max:200',
            'precio_compra' => 'sometimes|numeric|min:0',
            'precio_venta' => 'sometimes|numeric|min:0',
            'unidad_medida' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $producto->update($request->all());

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'producto' => $producto
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/productos/{id}",
     *     tags={"Productos"},
     *     summary="Desactivar producto",
     *     description="Desactiva un producto (soft delete)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Producto desactivado"),
     *     @OA\Response(response=404, description="Producto no encontrado")
     * )
     */
    public function destroy($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        // Soft delete - solo desactivar
        $producto->update(['activo' => false]);

        return response()->json([
            'message' => 'Producto desactivado exitosamente'
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/productos/buscar/{termino}",
     *     tags={"Productos"},
     *     summary="Buscar productos",
     *     description="Busca productos por nombre, código o molécula",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="termino",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="paracetamol")
     *     ),
     *     @OA\Response(response=200, description="Resultados de búsqueda")
     * )
     */
    public function buscar($termino)
    {
        $productos = Producto::where('nombre', 'ILIKE', "%{$termino}%")
            ->orWhere('codigo_producto', 'ILIKE', "%{$termino}%")
            ->orWhere('molecula', 'ILIKE', "%{$termino}%")
            ->where('activo', true)
            ->limit(20)
            ->get();

        return response()->json($productos, 200);
    }

    /**
     * Buscar producto por código de barras
     */
    public function porCodigoBarras($codigo)
    {
        $producto = Producto::where('codigo_barras', $codigo)
            ->where('activo', true)
            ->first();

        if (!$producto) {
            return response()->json([
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json($producto, 200);
    }

    /**
     * Exportar productos a Excel
     */
    public function exportExcel()
    {
        return Excel::download(new ProductosExport, 'productos_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Importar productos desde Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            Excel::import(new ProductosImport, $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Productos importados correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 422);
        }
    }
}
