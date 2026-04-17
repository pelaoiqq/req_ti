<?php
$path = '../';
require_once '../content/auth_check.php';
require_once '../content/db.php';

// Only Admin access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include '../includes/header.php';
include '../includes/sidebar.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $message = "Usuario eliminado correctamente.";
    } catch(PDOException $e) {
        $error = "Error al eliminar usuario: " . $e->getMessage();
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Gestión de Usuarios</h1>
    <a href="user_form.php" class="btn btn-primary btn-sm">
        <i class="ri-add-line" style="margin-right: 0.5rem;"></i> Nuevo Usuario
    </a>
</div>

<?php if(isset($message)): ?>
    <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Iniciales</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM users ORDER BY full_name ASC");
                    while($row = $stmt->fetch()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td><span class='badge' style='background: #e5e7eb; color: #374151;'>" . htmlspecialchars($row['short_name']) . "</span></td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td><span class='badge " . ($row['role'] == 'admin' ? 'badge-primary' : 'badge-success') . "'>" . htmlspecialchars($row['role']) . "</span></td>";
                        echo "<td>
                                <a href='user_form.php?id=" . $row['id'] . "' class='btn btn-sm' style='background: #eff6ff; color: #1d4ed8; margin-right: 0.5rem;'>
                                    <i class='ri-pencil-line'></i>
                                </a>
                                " . ($row['id'] != $_SESSION['user_id'] ? "
                                <a href='users.php?delete=" . $row['id'] . "' class='btn btn-sm' style='background: #fef2f2; color: #b91c1c;' onclick='return confirm(\"¿Está seguro de eliminar este usuario?\");'>
                                    <i class='ri-delete-bin-line'></i>
                                </a>" : "") . "
                              </td>";
                        echo "</tr>";
                    }
                } catch(PDOException $e) {
                    echo "<tr><td colspan='5'>Error cargando usuarios.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
