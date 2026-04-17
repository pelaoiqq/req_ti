<?php
$path = '../';
require_once '../content/auth_check.php';
require_once '../content/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entry_date = $_POST['entry_date'] ?? date('Y-m-d');
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $color = trim($_POST['color']);
    $colegio = $_POST['colegio'] ?? 'DP';
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if (empty($brand) || empty($model) || empty($color) || empty($colegio) || $quantity <= 0) {
        $error = 'Por favor complete todos los campos correctamente y asegúrese de que la cantidad sea mayor a 0.';
    } else {
        try {
            $pdo->beginTransaction();

            // Check if this exact ink exists
            $stmt = $pdo->prepare("SELECT id, total_quantity FROM inks WHERE brand = ? AND model = ? AND color = ? AND colegio = ?");
            $stmt->execute([$brand, $model, $color, $colegio]);
            $ink = $stmt->fetch();

            if ($ink) {
                // Update existing ink
                $ink_id = $ink['id'];
                $new_quantity = $ink['total_quantity'] + $quantity;

                $updateStmt = $pdo->prepare("UPDATE inks SET total_quantity = ? WHERE id = ?");
                $updateStmt->execute([$new_quantity, $ink_id]);
            } else {
                // Create new ink
                $insertInkStmt = $pdo->prepare("INSERT INTO inks (brand, model, color, colegio, total_quantity) VALUES (?, ?, ?, ?, ?)");
                $insertInkStmt->execute([$brand, $model, $color, $colegio, $quantity]);
                $ink_id = $pdo->lastInsertId();
            }

            // Create entry log
            $entryStmt = $pdo->prepare("INSERT INTO ink_entries (ink_id, quantity, entry_date, user_id) VALUES (?, ?, ?, ?)");
            $entryStmt->execute([$ink_id, $quantity, $entry_date, $user_id]);

            $pdo->commit();
            $success = "El ingreso de tinta ha sido registrado exitosamente.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error al guardar: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header d-flex justify-between align-center mb-4">
    <h1 class="page-title">Agregar Tinta</h1>
    <a href="stock_tintas.php" class="btn btn-sm" style="background: var(--bg-color); color: var(--text-muted);">
        <i class="ri-arrow-left-line"></i> Volver al Stock
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"
        style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"
        style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="">
        <div class="form-group mb-3">
            <label for="entry_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Fecha de
                Ingreso</label>
            <input type="date" id="entry_date" name="entry_date" class="form-control"
                value="<?php echo date('Y-m-d'); ?>" required
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
        </div>

        <div class="form-group mb-3">
            <label for="colegio" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Colegio</label>
            <select id="colegio" name="colegio" class="form-control" required
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                <option value="DP">Colegio DP</option>
                <option value="MC">Colegio MC</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="brand" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Marca</label>
            <input type="text" id="brand" name="brand" class="form-control" placeholder="Ej. Epson, HP, Canon" required
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
        </div>

        <div class="form-group mb-3">
            <label for="model" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Modelo</label>
            <input type="text" id="model" name="model" class="form-control" placeholder="Ej. 554, 664, CLI-150" required
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
        </div>

        <div class="form-group mb-3">
            <label for="color" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Color</label>
            <select id="color" name="color" class="form-control" required
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                <option value="">Seleccione un color...</option>
                <option value="Cyan">Cyan (Azul)</option>
                <option value="Magenta">Magenta (Rojo)</option>
                <option value="Yellow">Yellow (Amarillo)</option>
                <option value="Black">Black (Negro)</option>
            </select>
        </div>

        <div class="form-group mb-4">
            <label for="quantity" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Cantidad</label>
            <input type="number" id="quantity" name="quantity" class="form-control" min="1" value="1" required
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.75rem;">
            <i class="ri-save-line" style="margin-right: 0.5rem;"></i> Registrar Ingreso
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>