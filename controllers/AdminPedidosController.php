<?php
class AdminPedidosController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function latest(): void {
        $this->requireAdmin();

        $pedidos = $this->pdo->query(
            'SELECT p.*, u.nombre AS usuario_nombre, u.email AS usuario_email
             FROM pedidos p
             LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
             ORDER BY p.fecha_pedido DESC
             LIMIT 50'
        )->fetchAll();

        $totalPedidos = (int)$this->pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();

        json_response([
            'success' => true,
            'total' => $totalPedidos,
            'pedidos' => array_map([$this, 'serializarPedido'], $pedidos),
        ]);
    }

    public function detalle($idPedido = null): void {
        $this->requireAdmin();

        $idPedido = (int)$idPedido;
        if ($idPedido <= 0) {
            json_response(['success' => false, 'message' => 'Pedido invalido'], 400);
        }

        $stmt = $this->pdo->prepare(
            'SELECT p.*, u.nombre AS usuario_nombre, u.email AS usuario_email, u.direccion_envio
             FROM pedidos p
             LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
             WHERE p.id_pedido = :id_pedido'
        );
        $stmt->execute([':id_pedido' => $idPedido]);
        $pedido = $stmt->fetch();

        if (!$pedido) {
            json_response(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        $stmtDetalles = $this->pdo->prepare(
            'SELECT dp.*, pr.nombre AS producto_nombre, pr.imagen_url, pr.formato, pr.genero
             FROM detalles_pedido dp
             LEFT JOIN productos pr ON pr.id_producto = dp.id_producto
             WHERE dp.id_pedido = :id_pedido
             ORDER BY dp.id_detalle ASC'
        );
        $stmtDetalles->execute([':id_pedido' => $idPedido]);
        $detalles = $stmtDetalles->fetchAll();

        json_response([
            'success' => true,
            'pedido' => $this->serializarPedidoDetalle($pedido, $detalles),
        ]);
    }

    private function requireAdmin(): void {
        if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
            json_response(['success' => false, 'message' => 'No autorizado'], 403);
        }
    }

    private function serializarPedido(array $pedido): array {
        return [
            'id_pedido' => (int)($pedido['id_pedido'] ?? 0),
            'usuario_nombre' => (string)($pedido['usuario_nombre'] ?? ''),
            'usuario_email' => (string)($pedido['usuario_email'] ?? ''),
            'fecha_pedido' => (string)($pedido['fecha_pedido'] ?? ''),
            'total' => (float)($pedido['total'] ?? 0),
            'estado' => (string)($pedido['estado'] ?? 'pendiente'),
        ];
    }

    private function serializarPedidoDetalle(array $pedido, array $detalles): array {
        $data = $this->serializarPedido($pedido);
        $data['direccion_envio'] = (string)($pedido['direccion_envio'] ?? '');
        $data['items'] = array_map(function (array $detalle): array {
            $cantidad = (int)($detalle['cantidad'] ?? 0);
            $precio = (float)($detalle['precio_unitario'] ?? 0);

            return [
                'id_producto' => (int)($detalle['id_producto'] ?? 0),
                'nombre' => (string)($detalle['producto_nombre'] ?? 'Producto eliminado'),
                'imagen_url' => (string)($detalle['imagen_url'] ?? ''),
                'genero' => (string)($detalle['genero'] ?? ''),
                'formato' => (string)($detalle['formato'] ?? ''),
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => $cantidad * $precio,
            ];
        }, $detalles);

        return $data;
    }
}
