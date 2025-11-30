<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 8. LICENCIAS IMPORTACION
        Schema::create('licencias_importacion', function (Blueprint $table) {
            $table->id();
            $table->string('numero_licencia', 100)->unique();
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->integer('cantidad_autorizada');
            $table->integer('cantidad_importada')->default(0);
            $table->integer('saldo_pendiente');
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->string('lote_autorizado', 50)->nullable();
            $table->date('fecha_vencimiento_lote')->nullable();
            $table->string('codigo_paciente', 50)->nullable();
            $table->enum('tipo_licencia', ['inicial', 'prorroga_1', 'prorroga_2'])->nullable();
            $table->string('estado', 20)->default('vigente');
            $table->string('documento_soporte', 255)->nullable();
            $table->text('observaciones')->nullable();
        });

        // 9. AUTORIZACIONES INVIMA
        Schema::create('autorizaciones_invima', function (Blueprint $table) {
            $table->id();
            $table->string('numero_autorizacion', 100)->unique();
            $table->foreignId('paciente_id')->nullable()->constrained('pacientes');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->integer('cantidad_autorizada');
            $table->integer('cantidad_despachada')->default(0);
            $table->integer('saldo_pendiente');
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->string('lote_autorizado', 50)->nullable();
            $table->date('fecha_vencimiento_lote')->nullable();
            $table->string('estado', 20)->default('vigente');
            $table->string('documento_soporte', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
        });

        // 10. IMPORTACIONES
        Schema::create('importaciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero_importacion', 50)->unique();
            $table->foreignId('licencia_importacion_id')->nullable()->constrained('licencias_importacion');
            $table->foreignId('laboratorio_id')->nullable()->constrained('laboratorios');
            $table->date('fecha_importacion');
            $table->string('numero_declaracion', 100)->nullable();
            $table->string('numero_factura', 100)->nullable();
            $table->string('numero_guia', 100)->nullable();
            $table->decimal('valor_fob', 12, 2)->nullable();
            $table->decimal('valor_total', 12, 2);
            $table->string('documento_autorizacion', 255)->nullable();
            $table->string('documento_declaracion', 255)->nullable();
            $table->string('documento_factura', 255)->nullable();
            $table->string('documento_guia', 255)->nullable();
            $table->string('documento_licencia', 255)->nullable();
            $table->string('estado', 20)->default('en_tramite');
            $table->text('observaciones')->nullable();
        });

        // 11. DETALLE IMPORTACIONES
        Schema::create('detalle_importaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('importacion_id')->constrained('importaciones')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
        });

        // 12. CLIENTES MARKET
        Schema::create('clientes_market', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_cliente', 30)->nullable();
            $table->string('razon_social', 150);
            $table->string('nit', 50)->unique();
            $table->string('contacto_nombre', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion_entrega')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('persona_contacto', 100)->nullable();
            $table->string('sucursal', 100)->nullable();
            $table->unsignedBigInteger('vendedor_asignado_id')->nullable();
            $table->decimal('limite_credito', 12, 2)->default(0);
            $table->integer('dias_credito')->default(0);
            $table->boolean('cartera_al_dia')->default(true);
            $table->string('estado', 20)->default('activo');
            $table->timestamp('fecha_registro')->useCurrent();
        });

        // 13. VENDEDORES
        Schema::create('vendedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->string('codigo_vendedor', 20)->unique();
            $table->string('nombre_completo', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('zona_asignada', 100)->nullable();
            $table->decimal('comision_porcentaje', 5, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->date('fecha_ingreso')->useCurrent();
        });

        // 14. PROVEEDORES
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_proveedor', 150);
            $table->string('nit', 50)->unique();
            $table->string('contacto_nombre', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();
        });

        // 15. CONFIGURACION SISTEMA
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('parametro', 100)->unique();
            $table->text('valor')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('modulo', 20)->nullable();
            $table->string('tipo_dato', 20)->default('string');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('vendedores');
        Schema::dropIfExists('clientes_market');
        Schema::dropIfExists('detalle_importaciones');
        Schema::dropIfExists('importaciones');
        Schema::dropIfExists('autorizaciones_invima');
        Schema::dropIfExists('licencias_importacion');
    }
};
