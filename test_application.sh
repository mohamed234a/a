#!/bin/bash

echo "🚀 Testing Plateforme Auto-Entrepreneurs Tunisie"
echo "================================================"

BASE_URL="http://localhost:8000"

echo ""
echo "1. Testing Services API..."
SERVICES=$(curl -s "$BASE_URL/backend/index.php?route=api/services")
if echo "$SERVICES" | grep -q "site web"; then
    echo "✅ Services API working"
else
    echo "❌ Services API failed"
fi

echo ""
echo "2. Testing Auto-entrepreneurs API..."
AUTOENTREPRENEURS=$(curl -s "$BASE_URL/backend/index.php?route=api/autoentrepreneurs")
if echo "$AUTOENTREPRENEURS" | grep -q "Ali AE"; then
    echo "✅ Auto-entrepreneurs API working"
else
    echo "❌ Auto-entrepreneurs API failed"
fi

echo ""
echo "3. Testing Login API..."
LOGIN_RESPONSE=$(curl -s -X POST -H "Content-Type: application/json" \
    -d '{"email":"ae1@email.com","password":"password123"}' \
    "$BASE_URL/backend/index.php?route=api/auth/login")
if echo "$LOGIN_RESPONSE" | grep -q "token"; then
    echo "✅ Login API working"
    TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
else
    echo "❌ Login API failed"
    exit 1
fi

echo ""
echo "4. Testing Dashboard API..."
DASHBOARD=$(curl -s -H "Authorization: Bearer $TOKEN" \
    "$BASE_URL/backend/index.php?route=api/dashboard")
if echo "$DASHBOARD" | grep -q "services"; then
    echo "✅ Dashboard API working"
else
    echo "❌ Dashboard API failed"
fi

echo ""
echo "5. Testing Registration API..."
REGISTER_RESPONSE=$(curl -s -X POST -H "Content-Type: application/json" \
    -d '{"name":"Test User","email":"testuser@example.com","password":"password123","type":"client"}' \
    "$BASE_URL/backend/index.php?route=api/auth/register")
if echo "$REGISTER_RESPONSE" | grep -q "success"; then
    echo "✅ Registration API working"
else
    echo "❌ Registration API failed"
fi

echo ""
echo "6. Testing Frontend..."
FRONTEND=$(curl -s "$BASE_URL/frontend/index.html")
if echo "$FRONTEND" | grep -q "Plateforme Auto-Entrepreneurs Tunisie"; then
    echo "✅ Frontend loading"
else
    echo "❌ Frontend failed"
fi

echo ""
echo "🎉 All tests completed!"
echo ""
echo "📱 Access the application:"
echo "   Frontend: $BASE_URL/frontend/index.html"
echo "   Services: $BASE_URL/frontend/services.html"
echo "   Login:    $BASE_URL/frontend/login.html"
echo ""
echo "👥 Test users:"
echo "   Auto-entrepreneur: ae1@email.com / password123"
echo "   Client: client1@email.com / password123"
echo "   Admin: admin@email.com / password123"
