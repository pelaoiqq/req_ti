<?php
$path = '../';
require_once '../content/auth_check.php';
require_once '../content/db.php';

$error = '';
$success = '';

// Load available inks for the dropdown
$inks = [];
try {
    $stmt = $pdo->query("SELECT * FROM inks WHERE total_quantity > 0 ORDER BY brand ASC, model ASC, color ASC");
    $inks = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error al cargar las tintas: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exit_date = $_POST['exit_date'] ?? date('Y-m-d');
    $department = trim($_POST['department']);
    $ink_id = (int)($_POST['ink_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if (empty($department) || $ink_id <= 0 || $quantity <= 0) {
        $error = 'Por favor complete todos los campos y asegúrese de que la cantidad sea mayor a 0.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT total_quantity FROM inks WHERE id = ? FOR UPDATE");
            $stmt->execute([$ink_id]);
            $ink = $stmt->fetch();

            if (!$ink) {
                $error = "La tinta seleccionada no existe.";
                $pdo->rollBack();
            } elseif ($ink['total_quantity'] < $quantity) {
                $error = "Stock insuficiente. Stock actual: " . $ink['total_quantity'];
                $pdo->rollBack();
            } else {
                $new_quantity = $ink['total_quantity'] - $quantity;
                $updateStmt = $pdo->prepare("UPDATE inks SET total_quantity = ? WHERE id = ?");
                $updateStmt->execute([$new_quantity, $ink_id]);

                $exitStmt = $pdo->prepare("INSERT INTO ink_exits (ink_id, department, quantity, exit_date, user_id) VALUES (?, ?, ?, ?, ?)");
                $exitStmt->execute([$ink_id, $department, $quantity, $exit_date, $user_id]);

                $pdo->commit();
                $success = "La salida de tinta ha sido registrada exitosamente.";

                // Reload inks to reflect updated stock in dropdown
                $stmt = $pdo->query("SELECT * FROM inks WHERE total_quantity > 0 ORDER BY brand ASC, model ASC, color ASC");
                $inks = $stmt->fetchAll();
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            $error = "Error al guardar: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header d-flex justify-between align-center mb-4">
    <h1 class="page-title">Registrar Salida de Tinta</h1>
    <a href="stock_tintas.php" class="btn btn-sm" style="background: var(--bg-color); color: var(--text-muted);">
        <i class="ri-arrow-left-line"></i> Volver al Stock
    </a>
</div>

<?php if($error): ?>
    <div class="alert alert-danger" style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if($success): ?>
    <div class="alert alert-success" style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="">
        <div class="form-group mb-3">
            <label for="exit_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Fecha de Salida</label>
            <input type="date" id="exit_date" name="exit_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required 
                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
        </div>

        <div class="form-group mb-3">
            <label for="colegio" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Colegio</label>
            <select id="colegio" name="colegio" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;" onchange="filterInks()">
                <option value="DP">Colegio DP</option>
                <option value="MC">Colegio MC</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="department" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Departamento / Área</label>
            <select id="department" name="department" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                <option value="">Seleccione el departamento...</option>
                <option value="Dirección">Dirección</option>
                <option value="Inspectoría">Inspectoría</option>
                <option value="UTP">UTP</option>
                <option value="Secretaría">Secretaría</option>
                <option value="CRA">CRA</option>
                <option value="Laboratorio">Laboratorio</option>
                <option value="Pastoral">Pastoral</option>
                <option value="Orientación">Orientación</option>
                <option value="Convivencia Escolar">Convivencia Escolar</option>
                <option value="Otro">Otro (Especificar en notas)</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="ink_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Tinta</label>
            <select id="ink_id" name="ink_id" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                <option value="">Seleccione la tinta...</option>
                <?php foreach($inks as $ink): ?>
                    <option value="<?php echo $ink['id']; ?>" data-colegio="<?php echo htmlspecialchars($ink['colegio'] ?? 'DP'); ?>">
                        <?php echo htmlspecialchars($ink['brand'] . ' ' . $ink['model'] . ' - ' . $ink['color']); ?> 
                        (Stock: <?php echo $ink['total_quantity']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if(empty($inks)): ?>
                <small style="color: var(--danger); display: block; margin-top: 0.5rem;">No hay tintas con stock disponible actualmente.</small>
            <?php endif; ?>
        </div>

        <div class="form-group mb-4">
            <label for="quantity" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Cantidad Entregada</label>
            <input type="number" id="quantity" name="quantity" class="form-control" min="1" value="1" required 
                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
        </div>

        <button type="submit" class="btn btn-warning" style="width: 100%; justify-content: center; padding: 0.75rem; background: var(--warning); color: white; border: none;">
            <i class="ri-save-line" style="margin-right: 0.5rem;"></i> Registrar Salida
        </button>
    </form>
</div>

</div>

<script>
function filterInks() {
    var colegio = document.getElementById('colegio').value;
    var inkSelect = document.getElementById('ink_id');
    var options = inkSelect.options;

    // Reset selection
    inkSelect.value = '';

    for (var i = 1; i < options.length; i++) {
        var opt = options[i];
        if (opt.getAttribute('data-colegio') === colegio) {
            opt.style.display = '';
            opt.disabled = false;
        } else {
            opt.style.display = 'none';
            opt.disabled = true; // Disabled works better alongside display:none for cross-browser correctness
        }
    }
}
// Run once on load
document.addEventListener("DOMContentLoaded", function() {
    filterInks();
});
</script>

<?php include '../includes/footer.php'; ?>
