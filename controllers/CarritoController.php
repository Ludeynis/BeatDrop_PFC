<?php
class CarritoController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function index(): void {
        include BASE_PATH . '/views/carrito.php';
    }

    public function confirmar(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            json_response(['success' => false, 'message' => 'Metodo no permitido'], 405);
        }

        if (!isset($_SESSION['usuario_id'])) {
            json_response(['success' => false, 'message' => 'Debes iniciar sesion para finalizar la compra.'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            json_response(['success' => false, 'message' => 'Datos del pedido invalidos.'], 400);
        }

        $items = $data['items'] ?? [];
        if (!is_array($items) || count($items) === 0) {
            json_response(['success' => false, 'message' => 'El carrito esta vacio.'], 400);
        }

        $direccion = trim((string)($data['direccion'] ?? ''));

        try {
            $this->pdo->beginTransaction();

            if ($direccion !== '') {
                $stmtDireccion = $this->pdo->prepare(
                    'UPDATE usuarios SET direccion_envio = :direccion WHERE id_usuario = :id_usuario'
                );
                $stmtDireccion->execute([
                    ':direccion' => $direccion,
                    ':id_usuario' => (int)$_SESSION['usuario_id'],
                ]);
            }

            $lineas = [];
            $total = 0.0;

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $cantidad = max(1, (int)($item['cantidad'] ?? 1));
                $producto = $this->buscarProductoDelCarrito($item);

                if (!$producto) {
                    throw new RuntimeException('No se encontro uno de los productos del carrito.');
                }

                $stock = (int)($producto['stock'] ?? 0);
                if ($stock < $cantidad) {
                    throw new RuntimeException('Stock insuficiente para ' . (string)$producto['nombre'] . '.');
                }

                $precio = (float)($producto['precio'] ?? 0);
                $total += $precio * $cantidad;
                $lineas[] = [
                    'id_producto' => (int)$producto['id_producto'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                ];
            }

            if (count($lineas) === 0) {
                throw new RuntimeException('El carrito no contiene productos validos.');
            }

            $stmtPedido = $this->pdo->prepare(
                'INSERT INTO pedidos (id_usuario, total, estado) VALUES (:id_usuario, :total, "pendiente")'
            );
            $stmtPedido->execute([
                ':id_usuario' => (int)$_SESSION['usuario_id'],
                ':total' => $total,
            ]);
            $idPedido = (int)$this->pdo->lastInsertId();

            $stmtDetalle = $this->pdo->prepare(
                'INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario)
                 VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)'
            );
            $stmtStock = $this->pdo->prepare(
                'UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto'
            );

            foreach ($lineas as $linea) {
                $stmtDetalle->execute([
                    ':id_pedido' => $idPedido,
                    ':id_producto' => $linea['id_producto'],
                    ':cantidad' => $linea['cantidad'],
                    ':precio_unitario' => $linea['precio_unitario'],
                ]);
                $stmtStock->execute([
                    ':id_producto' => $linea['id_producto'],
                    ':cantidad' => $linea['cantidad'],
                ]);
            }

            $this->pdo->commit();

            json_response([
                'success' => true,
                'message' => 'Pedido confirmado correctamente.',
                'id_pedido' => $idPedido,
                'total' => $total,
                'fecha_estimada' => $this->rangoEntregaEstimado(),
            ]);
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            json_response([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    private function buscarProductoDelCarrito(array $item) {
        $idProducto = (int)($item['id_producto'] ?? $item['id'] ?? 0);
        if ($idProducto > 0) {
            $stmt = $this->pdo->prepare('SELECT * FROM productos WHERE id_producto = :id_producto FOR UPDATE');
            $stmt->execute([':id_producto' => $idProducto]);
            return $stmt->fetch();
        }

        $titulo = trim((string)($item['titulo'] ?? $item['nombre'] ?? ''));
        if ($titulo === '') {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM productos WHERE nombre = :nombre LIMIT 1 FOR UPDATE');
        $stmt->execute([':nombre' => $titulo]);
        return $stmt->fetch();
    }

    private function rangoEntregaEstimado(): string {
        $inicio = $this->sumarDiasHabiles(new DateTimeImmutable('today'), 3);
        $fin = $this->sumarDiasHabiles(new DateTimeImmutable('today'), 5);

        return $this->formatearFechaEntrega($inicio) . ' - ' . $this->formatearFechaEntrega($fin);
    }

    private function sumarDiasHabiles(DateTimeImmutable $fecha, int $dias): DateTimeImmutable {
        $resultado = $fecha;
        $sumados = 0;

        while ($sumados < $dias) {
            $resultado = $resultado->modify('+1 day');
            $diaSemana = (int)$resultado->format('N');
            if ($diaSemana < 6) {
                $sumados++;
            }
        }

        return $resultado;
    }

    private function formatearFechaEntrega(DateTimeImmutable $fecha): string {
        $dias = ['lun', 'mar', 'mie', 'jue', 'vie', 'sab', 'dom'];
        $meses = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre',
        ];

        $diaSemana = $dias[((int)$fecha->format('N')) - 1] ?? '';
        $diaMes = (int)$fecha->format('j');
        $mes = $meses[(int)$fecha->format('n')] ?? '';

        return trim($diaSemana . ', ' . $diaMes . ' ' . $mes);
    }
}
