<?php
$path = '../';
require_once '../content/auth_check.php';
require_once '../content/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header d-flex justify-between align-center mb-4">
    <h1 class="page-title">Stock de Tintas</h1>
    <div class="d-flex" style="gap: 1rem;">
        <a href="salida_tinta.php" class="btn btn-warning btn-sm" style="background: var(--warning); color: white; border: none;">
            <i class="ri-subtract-circle-line" style="margin-right: 0.5rem;"></i> Registrar Salida
        </a>
        <a href="agregar_tinta.php" class="btn btn-primary btn-sm">
            <i class="ri-add-circle-line" style="margin-right: 0.5rem;"></i> Ingresar Tinta
        </a>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Colegio</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Color</th>
                    <th>Stock Actual</th>
                    <th>Actualizado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql = "SELECT * FROM inks ORDER BY colegio ASC, brand ASC, model ASC, color ASC";
                    $stmt = $pdo->query($sql);
                    
                    if ($stmt->rowCount() > 0) {
                        while($row = $stmt->fetch()) {
                            // Determine stock status indicator
                            $stock_badge = 'badge-success';
                            if ($row['total_quantity'] == 0) {
                                $stock_badge = 'badge-danger';
                            } elseif ($row['total_quantity'] <= 5) {
                                $stock_badge = 'badge-warning';
                            }

                            echo "<tr>";
                            echo "<td><strong>" . htmlspecialchars($row['colegio'] ?? 'DP') . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['model']) . "</td>";
                            
                            // Visual color badge if standard color
                            $colorHex = '#e5e7eb'; // default grey
                            $colorText = '#374151';
                            $c = strtolower($row['color']);
                            if(strpos($c, 'cyan') !== false || strpos($c, 'cian') !== false) {
                                $colorHex = '#00FFFF'; $colorText = '#000';
                            } elseif(strpos($c, 'magenta') !== false) {
                                $colorHex = '#FF00FF'; $colorText = '#fff';
                            } elseif(strpos($c, 'yellow') !== false || strpos($c, 'amarillo') !== false) {
                                $colorHex = '#FFFF00'; $colorText = '#000';
                            } elseif(strpos($c, 'black') !== false || strpos($c, 'negro') !== false) {
                                $colorHex = '#000000'; $colorText = '#fff';
                            }
                            
                            echo "<td><span class='badge' style='background: {$colorHex}; color: {$colorText}; border: 1px solid #ccc;'>" . htmlspecialchars($row['color']) . "</span></td>";
                            echo "<td><span class='badge " . $stock_badge . "' style='font-size: 1rem;'>" . htmlspecialchars($row['total_quantity']) . "</span></td>";
                            echo "<td>" . date('d-m-Y H:i', strtotime($row['created_at'])) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-muted' style='text-align: center; padding: 2rem;'>No hay tintas registradas en stock. Registre un ingreso para comenzar.</td></tr>";
                    }
                } catch(PDOException $e) {
                    echo "<tr><td colspan='6'>Error cargando datos: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
