#!/bin/bash

echo "🧪 TESTING REPORTES API - Sistema AXXESS"
echo "========================================"
echo ""

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Base URL
BASE_URL="http://127.0.0.1:8002/api"

# 1. Login para obtener token
echo "1. 🔐 Obteniendo token de autenticación..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@glazgroup.com",
    "password": "Admin2025!"
  }')

TOKEN=$(echo $LOGIN_RESPONSE | python3 -c "import sys, json; print(json.load(sys.stdin)['token'])" 2>/dev/null)

if [ -z "$TOKEN" ]; then
    echo -e "${RED}❌ Error al obtener token${NC}"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✅ Token obtenido correctamente${NC}"
echo ""

# 2. Probar reportes
echo "2. 📊 Probando endpoints de reportes..."
echo ""

# Reporte Inventario
echo "   📦 Reporte de Inventario..."
INVENTARIO=$(curl -s -X GET "$BASE_URL/reportes/inventario" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

if echo "$INVENTARIO" | grep -q "resumen"; then
    echo -e "   ${GREEN}✅ Inventario OK${NC}"
    echo "$INVENTARIO" | python3 -m json.tool | head -20
else
    echo -e "   ${RED}❌ Inventario FALLO${NC}"
    echo "$INVENTARIO"
fi
echo ""

# Reporte Vencimientos
echo "   ⏰ Reporte de Vencimientos..."
VENCIMIENTOS=$(curl -s -X GET "$BASE_URL/reportes/vencimientos?dias=90" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

if echo "$VENCIMIENTOS" | grep -q "resumen"; then
    echo -e "   ${GREEN}✅ Vencimientos OK${NC}"
    echo "$VENCIMIENTOS" | python3 -m json.tool | head -15
else
    echo -e "   ${RED}❌ Vencimientos FALLO${NC}"
    echo "$VENCIMIENTOS"
fi
echo ""

# Reporte Autorizaciones INVIMA
echo "   📋 Reporte de Autorizaciones INVIMA..."
AUTORIZACIONES=$(curl -s -X GET "$BASE_URL/reportes/autorizaciones-invima" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

if echo "$AUTORIZACIONES" | grep -q "resumen"; then
    echo -e "   ${GREEN}✅ Autorizaciones OK${NC}"
    echo "$AUTORIZACIONES" | python3 -m json.tool | head -15
else
    echo -e "   ${RED}❌ Autorizaciones FALLO${NC}"
    echo "$AUTORIZACIONES"
fi
echo ""

# Reporte Ventas
echo "   💰 Reporte de Ventas..."
VENTAS=$(curl -s -X GET "$BASE_URL/reportes/ventas" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

if echo "$VENTAS" | grep -q "resumen"; then
    echo -e "   ${GREEN}✅ Ventas OK${NC}"
    echo "$VENTAS" | python3 -m json.tool | head -15
else
    echo -e "   ${RED}❌ Ventas FALLO${NC}"
    echo "$VENTAS"
fi
echo ""

# Reporte Despachos
echo "   🚚 Reporte de Despachos..."
DESPACHOS=$(curl -s -X GET "$BASE_URL/reportes/despachos" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

if echo "$DESPACHOS" | grep -q "resumen"; then
    echo -e "   ${GREEN}✅ Despachos OK${NC}"
    echo "$DESPACHOS" | python3 -m json.tool | head -15
else
    echo -e "   ${RED}❌ Despachos FALLO${NC}"
    echo "$DESPACHOS"
fi
echo ""

# Reporte Importaciones
echo "   📦 Reporte de Importaciones..."
IMPORTACIONES=$(curl -s -X GET "$BASE_URL/reportes/importaciones" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

if echo "$IMPORTACIONES" | grep -q "resumen"; then
    echo -e "   ${GREEN}✅ Importaciones OK${NC}"
    echo "$IMPORTACIONES" | python3 -m json.tool | head -15
else
    echo -e "   ${RED}❌ Importaciones FALLO${NC}"
    echo "$IMPORTACIONES"
fi
echo ""

echo "========================================"
echo -e "${GREEN}✅ Pruebas completadas${NC}"
echo ""
echo "📊 URLs de reportes:"
echo "   - Inventario: http://localhost:3001/reportes?tipo=inventario"
echo "   - Vencimientos: http://localhost:3001/reportes?tipo=vencimientos"
echo "   - Autorizaciones: http://localhost:3001/reportes?tipo=autorizaciones"
echo "   - Ventas: http://localhost:3001/reportes?tipo=ventas"
echo "   - Despachos: http://localhost:3001/reportes?tipo=despachos"
echo "   - Importaciones: http://localhost:3001/reportes?tipo=importaciones"
