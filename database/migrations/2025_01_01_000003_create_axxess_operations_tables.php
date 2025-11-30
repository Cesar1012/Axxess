<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 16. PEDIDOS
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido', 50)->unique();
            $table->string('tipo_pedido', 20)->nullable();
            $table->foreignId('cliente_market_id')->nullable()->constrained('clientes_market');
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes');
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores');
            $table->foreignId('usuario_registro_id')->nullable()->constrained('usuarios');
            $table->timestamp('fecha_pedido')->useCurrent();
            $table->date('fecha_entrega_programada')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('estado', 20)->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->boolean('requiere_nevera')->default(false);
            $table->boolean('requiere_gel')->default(false);
        });

        // 17. DETALLE PEDIDOS
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->string('codigo_paciente', 50)->nullable();
            $table->text('observaciones')->nullable();
        });

        // 18. DESPACHOS
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_remision', 50)->unique();
            $table->string('numero_guia', 50)->nullable();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos');
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes');
            $table->foreignId('cliente_market_id')->nullable()->constrained('clientes_market');
            $table->text('direccion_entrega');
            $table->timestamp('fecha_despacho')->useCurrent();
            $table->timestamp('fecha_entrega')->nullable();
            $table->string('transportista', 100)->nullable();
            $table->string('vehiculo_placa', 20)->nullable();
            $table->time('hora_cargue')->nullable();
            $table->boolean('requiere_cita')->default(false);
            $table->boolean('soporte_entrega')->default(false);
            $table->string('documento_soporte', 255)->nullable();
            $table->string('estado', 20)->default('preparado');
            $table->string('firma_recibido', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_preparo_id')->nullable()->constrained('usuarios');
        });

        // 19. DETALLE DESPACHOS
        Schema::create('detalle_despachos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despacho_id')->constrained('despachos')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');
            $table->integer('cantidad');
            $table->foreignId('autorizacion_invima_id')->nullable()->constrained('autorizaciones_invima');
            $table->date('fecha_vencimiento_lote')->nullable();
        });

        // 20. ENTREGAS PACIENTES
        Schema::create('entregas_pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes');
            $table->foreignId('despacho_id')->nullable()->constrained('despachos');
            $table->foreignId('autorizacion_invima_id')->nullable()->constrained('autorizaciones_invima');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');
            $table->integer('cantidad_viales');
            $table->date('fecha_entrega');
            $table->date('fecha_aplicacion')->nullable();
            $table->integer('viales_utilizados_terapia')->nullable();
            $table->boolean('requirio_acondicionamiento')->default(false);
            $table->boolean('insumos_nevera')->default(false);
            $table->boolean('insumos_gel')->default(false);
            $table->decimal('costo_insumos', 10, 2)->default(0);
            $table->decimal('comision_entrega', 10, 2)->default(0);
            $table->string('documento_soporte', 255)->nullable();
            $table->text('observaciones')->nullable();
        });

        // 21. VENTAS
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_factura', 50)->unique();
            $table->string('tipo_venta', 20)->nullable();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos');
            $table->foreignId('despacho_id')->nullable()->constrained('despachos');
            $table->foreignId('cliente_market_id')->nullable()->constrained('clientes_market');
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes');
            $table->foreignId('vendedor_id')->nullable()->constrained('vendedores');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->timestamp('fecha_venta')->useCurrent();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('forma_pago', 20)->default('contado');
            $table->string('estado', 20)->default('completada');
            $table->text('observaciones')->nullable();
        });

        // 22. DETALLE VENTAS
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('entregas_pacientes');
        Schema::dropIfExists('detalle_despachos');
        Schema::dropIfExists('despachos');
        Schema::dropIfExists('detalle_pedidos');
        Schema::dropIfExists('pedidos');
    }
};
