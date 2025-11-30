<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 23. CUENTAS POR COBRAR
        Schema::create('cuentas_por_cobrar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->nullable()->constrained('ventas');
            $table->string('numero_factura', 50);
            $table->foreignId('cliente_market_id')->nullable()->constrained('clientes_market');
            $table->decimal('valor_total', 12, 2);
            $table->decimal('valor_pagado', 12, 2)->default(0);
            $table->decimal('saldo_pendiente', 12, 2);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->integer('dias_mora')->default(0);
            $table->string('estado', 20)->default('vigente');
        });

        // 24. RECAUDOS
        Schema::create('recaudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_cobrar_id')->nullable()->constrained('cuentas_por_cobrar');
            $table->string('numero_factura', 50);
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores');
            $table->date('fecha_recaudo');
            $table->decimal('valor_recaudado', 12, 2);
            $table->string('forma_pago', 20)->nullable();
            $table->string('numero_transaccion', 100)->nullable();
            $table->string('documento_soporte', 255)->nullable();
            $table->text('observaciones')->nullable();
        });

        // 25. RUTAS ENTREGA
        Schema::create('rutas_entrega', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_ruta', 100);
            $table->date('fecha_ruta');
            $table->string('zona', 100)->nullable();
            $table->string('transportista', 100)->nullable();
            $table->string('vehiculo_placa', 20)->nullable();
            $table->time('hora_salida')->nullable();
            $table->string('estado', 20)->default('planificada');
            $table->text('observaciones')->nullable();
        });

        // 26. DESPACHOS RUTA
        Schema::create('despachos_ruta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained('rutas_entrega')->onDelete('cascade');
            $table->foreignId('despacho_id')->nullable()->constrained('despachos');
            $table->integer('orden_entrega');
            $table->time('hora_estimada')->nullable();
        });

        // 27. COMPRAS
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('numero_compra', 50)->unique();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->date('fecha_compra');
            $table->string('numero_factura', 100)->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('estado', 20)->default('pendiente');
            $table->text('observaciones')->nullable();
        });

        // 28. DETALLE COMPRAS
        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
        });

        // 29. ALERTAS
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_alerta', 50)->nullable();
            $table->string('modulo', 20)->nullable();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onDelete('cascade');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->onDelete('cascade');
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('cliente_market_id')->nullable()->constrained('clientes_market')->onDelete('cascade');
            $table->foreignId('autorizacion_invima_id')->nullable()->constrained('autorizaciones_invima')->onDelete('cascade');
            $table->foreignId('licencia_importacion_id')->nullable()->constrained('licencias_importacion')->onDelete('cascade');
            $table->text('mensaje');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->date('fecha_vencimiento_alerta')->nullable();
            $table->string('prioridad', 20)->default('media');
            $table->boolean('leida')->default(false);
            $table->boolean('resuelta')->default(false);
            $table->boolean('enviar_email')->default(false);
            $table->boolean('email_enviado')->default(false);
            $table->text('destinatarios_email')->nullable();
        });

        // 30. MOVIMIENTOS INVENTARIO (KARDEX)
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');
            $table->foreignId('bodega_id')->nullable()->constrained('bodegas');
            $table->string('tipo_movimiento', 30)->nullable();
            $table->integer('cantidad');
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia_tipo', 50)->nullable();
            $table->decimal('costo_unitario', 12, 2)->nullable();
            $table->decimal('valor_total', 12, 2)->nullable();
            $table->integer('stock_anterior');
            $table->integer('stock_posterior');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->timestamp('fecha_movimiento')->useCurrent();
            $table->text('observaciones')->nullable();
        });

        // 31. AUDITORIA
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->string('modulo', 50);
            $table->string('accion', 100);
            $table->string('tabla_afectada', 50);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->timestamp('fecha_hora')->useCurrent();
        });

        // 32. REPORTES GENERADOS
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_reporte', 100);
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->string('modulo', 20)->nullable();
            $table->timestamp('fecha_generacion')->useCurrent();
            $table->json('parametros')->nullable();
            $table->string('archivo_resultado', 255)->nullable();
            $table->boolean('enviado_email')->default(false);
            $table->string('email_destinatario', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
        Schema::dropIfExists('auditoria');
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('alertas');
        Schema::dropIfExists('detalle_compras');
        Schema::dropIfExists('compras');
        Schema::dropIfExists('despachos_ruta');
        Schema::dropIfExists('rutas_entrega');
        Schema::dropIfExists('recaudos');
        Schema::dropIfExists('cuentas_por_cobrar');
    }
};
