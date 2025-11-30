<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Sistema AXXESS API",
 *     description="API REST para el Sistema de Gestión AXXESS - GLAZ GROUP S.A.S.",
 *     @OA\Contact(
 *         email="soporte@glazgroup.com",
 *         name="GLAZ GROUP S.A.S."
 *     ),
 *     @OA\License(
 *         name="Confidencial",
 *         url="https://glazgroup.com/licencia"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor de Desarrollo"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Ingrese el token de autenticación Sanctum"
 * )
 *
 * @OA\Tag(
 *     name="Autenticación",
 *     description="Endpoints de autenticación y gestión de sesiones"
 * )
 *
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Estadísticas y métricas del sistema"
 * )
 *
 * @OA\Tag(
 *     name="Productos",
 *     description="Gestión de productos (THERAPIES y MARKET)"
 * )
 *
 * @OA\Tag(
 *     name="Therapies - Pacientes",
 *     description="Módulo THERAPIES - Gestión de pacientes"
 * )
 *
 * @OA\Tag(
 *     name="Market - Clientes",
 *     description="Módulo MARKET - Gestión de clientes"
 * )
 *
 * @OA\Tag(
 *     name="Market - Pedidos",
 *     description="Módulo MARKET - Gestión de pedidos"
 * )
 *
 * @OA\Tag(
 *     name="Market - Vendedores",
 *     description="Módulo MARKET - Gestión de vendedores"
 * )
 *
 * @OA\Tag(
 *     name="Inventario - Laboratorios",
 *     description="Gestión de laboratorios farmacéuticos"
 * )
 *
 * @OA\Tag(
 *     name="Inventario - Bodegas",
 *     description="Gestión de bodegas de almacenamiento"
 * )
 *
 * @OA\Tag(
 *     name="Inventario - Lotes",
 *     description="Gestión de lotes de productos"
 * )
 *
 * @OA\Tag(
 *     name="Compras",
 *     description="Gestión de compras y proveedores"
 * )
 *
 * @OA\Tag(
 *     name="Licencias e Importaciones",
 *     description="Gestión de licencias de importación"
 * )
 *
 * @OA\Tag(
 *     name="Autorizaciones INVIMA",
 *     description="Gestión de autorizaciones INVIMA"
 * )
 *
 * @OA\Tag(
 *     name="Despachos",
 *     description="Gestión de despachos y entregas"
 * )
 *
 * @OA\Tag(
 *     name="Ventas",
 *     description="Gestión de ventas y facturación"
 * )
 *
 * @OA\Tag(
 *     name="Finanzas",
 *     description="Cuentas por cobrar y recaudos"
 * )
 *
 * @OA\Tag(
 *     name="Alertas",
 *     description="Sistema de alertas y notificaciones"
 * )
 *
 * @OA\Tag(
 *     name="Auditoría",
 *     description="Registro de auditoría del sistema"
 * )
 *
 * @OA\Tag(
 *     name="Configuración",
 *     description="Configuración del sistema"
 * )
 */
abstract class Controller
{
    //
}
