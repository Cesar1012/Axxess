<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. USUARIOS
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->enum('rol', ['administrador', 'control', 'consulta', 'ejecutivo_comercial'])->nullable();
            $table->enum('modulo_acceso', ['axxess_therapies', 'axxess_market', 'ambos'])->nullable();
            $table->string('telefono', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('ultimo_acceso')->nullable();
            $table->index('email');
        });

        // 2. PACIENTES
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_paciente', 50)->unique();
            $table->string('nombre_completo', 150);
            $table->string('tipo_documento', 20);
            $table->string('numero_documento', 50)->unique();
            $table->string('eps_asegurador', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion_entrega')->nullable();
            $table->string('zona_cobertura', 100)->nullable();
            $table->boolean('requiere_cita_entrega')->default(false);
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();
        });

        // 3. LABORATORIOS
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_laboratorio', 150);
            $table->string('nit', 50)->unique();
            $table->string('pais_origen', 50)->nullable();
            $table->string('contacto_nombre', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();
        });

        // 4. CATEGORIAS
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo_modulo', ['therapies', 'market', 'ambos'])->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
        });

        // 5. BODEGAS
        Schema::create('bodegas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_bodega', 100);
            $table->string('ubicacion_ciudad', 100);
            $table->text('direccion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->index('ubicacion_ciudad');
        });

        // 6. PRODUCTOS
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_producto', 50)->unique();
            $table->string('codigo_barras', 50)->nullable();
            $table->string('nombre', 200);
            $table->string('molecula', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->enum('modulo', ['therapies', 'market'])->nullable();
            $table->decimal('precio_compra', 12, 2);
            $table->decimal('precio_venta', 12, 2);
            $table->decimal('precio_mayorista', 12, 2)->nullable();
            $table->integer('stock_minimo')->default(0);
            $table->integer('stock_actual')->default(0);
            $table->string('unidad_medida', 20);
            $table->boolean('requiere_cadena_frio')->default(false);
            $table->boolean('requiere_acondicionamiento')->default(false);
            $table->string('lote_inicial', 50)->nullable();
            $table->date('fecha_vencimiento_inicial')->nullable();
            $table->decimal('impuesto_iva', 5, 2)->default(0);
            $table->string('registro_sanitario', 100)->nullable();
            $table->date('fecha_vencimiento_registro')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        // 7. LOTES
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('numero_lote', 50);
            $table->date('fecha_fabricacion');
            $table->date('fecha_vencimiento');
            $table->integer('cantidad_inicial');
            $table->integer('cantidad_actual');
            $table->foreignId('bodega_id')->nullable()->constrained('bodegas');
            $table->foreignId('laboratorio_id')->nullable()->constrained('laboratorios');
            $table->string('deposito_llegada', 100)->nullable();
            $table->boolean('acondicionado')->default(false);
            $table->string('estado', 20)->default('disponible');
            $table->string('foto_producto', 255)->nullable();
            $table->unique(['producto_id', 'numero_lote']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('bodegas');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('laboratorios');
        Schema::dropIfExists('pacientes');
        Schema::dropIfExists('usuarios');
    }
};
