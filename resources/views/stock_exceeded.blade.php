<!DOCTYPE html>
<html>
<head>
    <title>Demanda alta de productos</title>
</head>
<body>
    <h1>Hola,</h1>
    <p>Por el momento tenemos una gran demanda de pedidos para el producto: {{ $item->name }}.</p>
    <p>Sé paciente con tu pedido de {{ $requestedQuantity }} unidades.</p>
    <p>Gracias por tu comprensión.</p>
</body>
</html>