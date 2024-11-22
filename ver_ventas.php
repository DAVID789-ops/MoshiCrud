<?php include_once "encabezado.php"; ?>
<?php
include_once "funciones.php";

function obtenerVentas($offset, $limit) {
    $bd = obtenerBD();
    $sentencia = $bd->prepare("SELECT v.id, c.nombre AS nombre_cliente, c.telefono, v.monto, v.fecha, v.descripcion, v.tamaño, v.cantidad, v.ganancia, v.nombres_perfumes, v.mensajero, v.precio_envio, v.direccion_envio, v.hecho 
                              FROM ventas_clientes v
                              JOIN clientes c ON v.id_cliente = c.id
                              ORDER BY v.fecha DESC LIMIT ? OFFSET ?");
    $sentencia->bindParam(1, $limit, PDO::PARAM_INT);
    $sentencia->bindParam(2, $offset, PDO::PARAM_INT);
    $sentencia->execute();
    return $sentencia->fetchAll(PDO::FETCH_ASSOC);
}

function contarVentas() {
    $bd = obtenerBD();
    $sentencia = $bd->query("SELECT COUNT(*) as total FROM ventas_clientes");
    return $sentencia->fetch(PDO::FETCH_ASSOC)['total'];
}

function actualizarHecho($ventaId, $hecho) {
    $bd = obtenerBD();
    $sentencia = $bd->prepare("UPDATE ventas_clientes SET hecho = :hecho WHERE id = :id");
    $sentencia->bindParam(':hecho', $hecho, PDO::PARAM_INT);
    $sentencia->bindParam(':id', $ventaId, PDO::PARAM_INT);
    return $sentencia->execute();
}

$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hecho'])) {
    $ventaId = $_POST['venta_id'];
    $hecho = $_POST['hecho'] == 'true' ? 1 : 0;
    actualizarHecho($ventaId, $hecho);
    // Redirigir para evitar reenvío del formulario
    header("Location: ver_ventas.php?page=$page");
    exit();
}

$ventas = obtenerVentas($offset, $limit);
$totalVentas = contarVentas();
$totalPages = ceil($totalVentas / $limit);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ver Ventas</title>
    <link rel="stylesheet" href="styles.css"> <!-- Incluye tu archivo CSS aquí -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            padding: 8px 16px;
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            color: #333;
        }
        .pagination a.active {
            background-color: #333;
            color: #fff;
            border: 1px solid #333;
        }
        .acciones {
            display: flex;
            justify-content: space-around;
        }
        .acciones a, .acciones button {
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #ccc;
            background-color: #f2f2f2;
            color: #333;
            cursor: pointer;
        }
        .acciones a:hover, .acciones button:hover {
            background-color: #e0e0e0;
        }
        .acciones button.realizado {
            background-color: green;
            color: white;
        }
        .acciones button.no-realizado {
            background-color: red;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Listado de Ventas</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Cliente</th>
                <th>Teléfono</th>
                <th>Monto</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Tamaño</th>
                <th>Cantidad</th>
                <th>Ganancia</th>
                <th>Nombres de Perfumes</th>
                <th>Mensajero</th>
                <th>Precio Envío</th>
                <th>Dirección Envío</th>
                <th>Hecho</th> <!-- Nueva columna -->
                <th>Acciones</th> <!-- Columna para acciones -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventas as $venta) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($venta['id']); ?></td>
                    <td><?php echo htmlspecialchars($venta['nombre_cliente']); ?></td>
                    <td><?php echo htmlspecialchars($venta['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($venta['monto']); ?></td>
                    <td><?php echo htmlspecialchars($venta['fecha']); ?></td>
                    <td><?php echo htmlspecialchars($venta['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($venta['tamaño']); ?></td>
                    <td><?php echo htmlspecialchars($venta['cantidad']); ?></td>
                    <td><?php echo htmlspecialchars($venta['ganancia']); ?></td>
                    <td><?php echo htmlspecialchars($venta['nombres_perfumes']); ?></td>
                    <td><?php echo htmlspecialchars($venta['mensajero']); ?></td>
                    <td><?php echo htmlspecialchars($venta['precio_envio']); ?></td>
                    <td><?php echo htmlspecialchars($venta['direccion_envio']); ?></td>
                    <td>
                        <button class="estado-venta <?php echo $venta['hecho'] == 1 ? 'realizado' : 'no-realizado'; ?>" data-id="<?php echo $venta['id']; ?>" data-estado="<?php echo $venta['hecho']; ?>">
                            <?php echo $venta['hecho'] == 1 ? 'Realizado' : 'Marcar'; ?>
                        </button>
                    </td>
                    <td class="acciones">
                        <a href="editar_venta.php?id=<?php echo $venta['id']; ?>">Editar</a>
                        <a href="eliminar_venta.php?id=<?php echo $venta['id']; ?>">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">&laquo; Anterior</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Siguiente &raquo;</a>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.estado-venta').forEach(button => {
            button.addEventListener('click', function() {
                const ventaId = this.getAttribute('data-id');
                const estadoActual = parseInt(this.getAttribute('data-estado'));

                fetch('ver_ventas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'venta_id=' + ventaId + '&hecho=' + (estadoActual === 1 ? 'false' : 'true'),
                })
                .then(response => response.text())
                .then(data => {
                    // Actualizar el texto y color del botón
                    if (estadoActual === 1) {
                        this.innerText = 'Marcar';
                        this.classList.remove('realizado');
                        this.classList.add('no-realizado');
                    } else {
                        this.innerText = 'Realizado';
                        this.classList.remove('no-realizado');
                        this.classList.add('realizado');
                    }
                    // Cambiar el estado actual para el próximo clic
                    this.setAttribute('data-estado', estadoActual === 1 ? 0 : 1);
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>

<?php include_once "pie.php"; ?>
