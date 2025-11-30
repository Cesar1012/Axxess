<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'codigo_producto',
        'codigo_barras',
        'nombre',
        'molecula',
        'descripcion',
        'categoria_id',
        'modulo',
        'precio_compra',
        'precio_venta',
        'precio_mayorista',
        'stock_minimo',
        'stock_actual',
        'unidad_medida',
        'requiere_cadena_frio',
        'requiere_acondicionamiento',
        'lote_inicial',
        'fecha_vencimiento_inicial',
        'impuesto_iva',
        'registro_sanitario',
        'fecha_vencimiento_registro',
        'activo',
        'fecha_creacion'
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'precio_mayorista' => 'decimal:2',
        'stock_minimo' => 'integer',
        'stock_actual' => 'integer',
        'requiere_cadena_frio' => 'boolean',
        'requiere_acondicionamiento' => 'boolean',
        'fecha_vencimiento_inicial' => 'date',
        'impuesto_iva' => 'decimal:2',
        'fecha_vencimiento_registro' => 'date',
        'activo' => 'boolean',
        'fecha_creacion' => 'datetime'
    ];

    /**
     * Relaciones
     */

    // Pertenece a una categoría
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    // Tiene muchos lotes
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'producto_id');
    }

    // Tiene muchas licencias de importación
    public function licenciasImportacion(): HasMany
    {
        return $this->hasMany(LicenciaImportacion::class, 'producto_id');
    }

    // Tiene muchas autorizaciones INVIMA
    public function autorizacionesInvima(): HasMany
    {
        return $this->hasMany(AutorizacionInvima::class, 'producto_id');
    }

    // Tiene muchos detalles de importación
    public function detallesImportacion(): HasMany
    {
        return $this->hasMany(DetalleImportacion::class, 'producto_id');
    }

    // Tiene muchos detalles de pedido
    public function detallesPedido(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'producto_id');
    }

    // Tiene muchos detalles de despacho
    public function detallesDespacho(): HasMany
    {
        return $this->hasMany(DetalleDespacho::class, 'producto_id');
    }

    // Tiene muchas entregas a pacientes
    public function entregasPacientes(): HasMany
    {
        return $this->hasMany(EntregaPaciente::class, 'producto_id');
    }

    // Tiene muchos detalles de venta
    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    // Tiene muchas alertas
    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'producto_id');
    }

    // Tiene muchos movimientos de inventario
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    // Tiene muchos detalles de compra
    public function detallesCompra(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'producto_id');
    }

    /**
     * Scopes
     */

    // Filtrar productos activos
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // Filtrar por módulo (therapies o market)
    public function scopePorModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    // Filtrar productos con stock bajo
    public function scopeStockBajo($query)
    {
        return $query->whereRaw('stock_actual <= stock_minimo');
    }

    // Filtrar productos que requieren cadena de frío
    public function scopeRequiereCadenaFrio($query)
    {
        return $query->where('requiere_cadena_frio', true);
    }

    // Filtrar productos que requieren acondicionamiento
    public function scopeRequiereAcondicionamiento($query)
    {
        return $query->where('requiere_acondicionamiento', true);
    }
}
