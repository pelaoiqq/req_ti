<?php
$path = '../';
require_once '../content/auth_check.php';
require_once '../content/db.php';

include '../includes/header.php';
include '../includes/sidebar.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;
$req = [
    'school' => 'DP',
    'description' => '',
    'responsible_id' => '',
    'start_date' => date('Y-m-d'),
    'end_date' => '',
    'status' => 'Abierto',
    'priority' => 'Normal'
];

$is_edit = false;
$observations = [];

if ($id) {
    $is_edit = true;
    try {
        // Fetch Requirement
        $stmt = $pdo->prepare("SELECT * FROM requirements WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $req = $stmt->fetch();
        
        if (!$req) {
            echo "<script>window.location='requirements.php';</script>";
            exit;
        }

        // Fetch Observations
        $obs_sql = "SELECT o.*, u.short_name 
                   FROM observations o 
                   JOIN users u ON o.user_id = u.id 
                   WHERE o.requirement_id = :id 
                   ORDER BY o.created_at ASC";
        $obs_stmt = $pdo->prepare($obs_sql);
        $obs_stmt->bindParam(':id', $id);
        $obs_stmt->execute();
        $observations = $obs_stmt->fetchAll();

    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Update/Create Requirement (ADMIN ONLY)
    if (isset($_POST['save_requirement'])) {
        if ($_SESSION['role'] !== 'admin') {
            die("Acceso denegado.");
        }

        $school = $_POST['school'];
        $description = $_POST['description'];
        $responsible_id = $_POST['responsible_id'];
        $start_date = $_POST['start_date'];
        $priority = $_POST['priority'];
        
        // Status handling (Closing logic)
        $new_status = $_POST['status'];
        $end_date = null;
        
        if ($new_status == 'Cerrado') {
             // If already closed, keep existing date, otherwise set to today
             if ($req['status'] == 'Cerrado' && !empty($req['end_date'])) {
                 $end_date = $req['end_date'];
             } else {
                 $end_date = date('Y-m-d');
             }
        } else {
            $end_date = null;
        }
        
        try {
            if ($is_edit) {
                $sql = "UPDATE requirements SET school=:school, description=:description, responsible_id=:responsible_id, start_date=:start_date, end_date=:end_date, status=:status, priority=:priority WHERE id=:id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':school', $school);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':responsible_id', $responsible_id);
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':end_date', $end_date);
                $stmt->bindParam(':status', $new_status);
                $stmt->bindParam(':priority', $priority);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                // Add "Closed" observation automatically if status changed to Closed
                if ($req['status'] == 'Abierto' && $new_status == 'Cerrado') {
                    $close_obs = date('d-m') . " Se cierra requerimiento.";
                    $obs_stmt = $pdo->prepare("INSERT INTO observations (requirement_id, user_id, observation_text) VALUES (:rid, :uid, :text)");
                    $obs_stmt->bindValue(':rid', $id);
                    $obs_stmt->bindValue(':uid', $_SESSION['user_id']);
                    $obs_stmt->bindValue(':text', $close_obs);
                    $obs_stmt->execute();
                }

                echo "<script>window.location='requirement_form.php?id=$id';</script>";

            } else {
                $status = 'Abierto';
                $sql = "INSERT INTO requirements (school, description, responsible_id, start_date, status, priority) VALUES (:school, :description, :responsible_id, :start_date, :status, :priority)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':school', $school);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':responsible_id', $responsible_id);
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':priority', $priority);
                $stmt->execute();
                $new_id = $pdo->lastInsertId();
                echo "<script>window.location='requirement_form.php?id=$new_id';</script>";
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }

    // 2. Add Observation
    if (isset($_POST['add_observation']) && $is_edit) {
        $obs_text = trim($_POST['observation_text']);
        if (!empty($obs_text)) {
            // Format: "DD-MM Texto" logic is handled by pre-pending date if user didn't type it, 
            // OR we just store the text and in the logic we have the created_at. 
            // User requested: "03-01 Se cotizo..." format in the text itself.
            // Let's prepend the date automatically for consistency.
            
            $current_date = date('d-m');
            $final_text = "$current_date $obs_text";
            
            try {
                $sql = "INSERT INTO observations (requirement_id, user_id, observation_text) VALUES (:rid, :uid, :text)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':rid', $id);
                $stmt->bindParam(':uid', $_SESSION['user_id']);
                $stmt->bindParam(':text', $final_text);
                $stmt->execute();
                echo "<script>window.location='requirement_form.php?id=$id';</script>";
            } catch(PDOException $e) {
                $error = "Error agregando observación: " . $e->getMessage();
            }
        }
    }
}

// Redirect non-admins trying to create new
if (!$is_edit && $_SESSION['role'] !== 'admin') {
    header("Location: requirements.php");
    exit();
}

$disabled = ($_SESSION['role'] !== 'admin') ? 'disabled' : '';
?>

<div class="page-header">
    <h1 class="page-title"><?php echo $is_edit ? 'Detalle Requerimiento #' . $id : 'Nuevo Requerimiento'; ?></h1>
    <a href="requirements.php" class="btn btn-sm" style="background: var(--bg-color); color: var(--text-muted);">
        <i class="ri-arrow-left-line"></i> Volver
    </a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
    
    <!-- Left Column: Form -->
    <div class="card">
        <form action="" method="post">
            <div class="d-flex gap-2" style="margin-bottom: 1rem;">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Colegio</label>
                    <select name="school" class="form-control" <?php echo $disabled; ?>>
                        <option value="DP" <?php echo $req['school'] == 'DP' ? 'selected' : ''; ?>>DP</option>
                        <option value="MC" <?php echo $req['school'] == 'MC' ? 'selected' : ''; ?>>MC</option>
                        <option value="IQ" <?php echo $req['school'] == 'IQ' ? 'selected' : ''; ?>>IQ</option>
                    </select>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Prioridad</label>
                    <select name="priority" class="form-control" <?php echo $disabled; ?>>
                        <option value="Normal" <?php echo $req['priority'] == 'Normal' ? 'selected' : ''; ?>>Normal</option>
                        <option value="Alta" <?php echo $req['priority'] == 'Alta' ? 'selected' : ''; ?>>Alta</option>
                        <option value="Critica" <?php echo $req['priority'] == 'Critica' ? 'selected' : ''; ?>>Critica</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Descripción del Requerimiento</label>
                <textarea name="description" class="form-control" rows="3" required <?php echo $disabled; ?>><?php echo htmlspecialchars($req['description']); ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Responsable</label>
                    <select name="responsible_id" class="form-control" required <?php echo $disabled; ?>>
                        <option value="">Seleccionar...</option>
                        <?php
                        $users = $pdo->query("SELECT id, full_name, short_name FROM users ORDER BY short_name ASC");
                        while($u = $users->fetch()) {
                            $sel = $req['responsible_id'] == $u['id'] ? 'selected' : '';
                            echo "<option value='".$u['id']."' $sel>".$u['full_name']." (".$u['short_name'].")</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $req['start_date']; ?>" required <?php echo $disabled; ?>>
                </div>
            </div>

            <?php if($is_edit): ?>
            <div class="d-flex gap-2">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-control" <?php echo $disabled; ?>>
                        <option value="Abierto" <?php echo $req['status'] == 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                        <option value="Cerrado" <?php echo $req['status'] == 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                    </select>
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">Fecha Termino</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $req['end_date']; ?>" disabled placeholder="Al cerrar">
                </div>
            </div>
            <?php endif; ?>

            <?php if($_SESSION['role'] == 'admin'): ?>
            <div class="mt-4 text-right">
                <button type="submit" name="save_requirement" class="btn btn-primary">
                    <?php echo $is_edit ? 'Guardar Cambios' : 'Crear Requerimiento'; ?>
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Right Column: Observations (Only existing reqs) -->
    <?php if($is_edit): ?>
    <div class="card">
        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">Observaciones / Bitácora</h3>
        
        <div style="max-height: 400px; overflow-y: auto; margin-bottom: 1.5rem;">
            <?php if(count($observations) > 0): ?>
                <?php foreach($observations as $obs): ?>
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <div style="font-size: 0.9rem; margin-bottom: 0.25rem;">
                            <?php echo htmlspecialchars($obs['observation_text']); ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            <i class="ri-user-line"></i> <?php echo htmlspecialchars($obs['short_name']); ?> | 
                            <?php echo date('d-m-Y H:i', strtotime($obs['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="color: var(--text-muted); font-size: 0.9rem; font-style: italic;">No hay observaciones registradas.</div>
            <?php endif; ?>
        </div>

        <form action="" method="post">
            <div class="form-group">
                <label class="form-label">Agregar Observación</label>
                <textarea name="observation_text" class="form-control" rows="2" placeholder="Ej: Se cotizó con proveedor..." required></textarea>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                    * La fecha (<?php echo date('d-m'); ?>) se agregará automáticamente al inicio.
                </div>
            </div>
            <button type="submit" name="add_observation" class="btn btn-sm btn-primary" style="width: 100%;">Agregar Nota</button>
        </form>
    </div>
    <?php else: ?>
        <div class="card" style="display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
            <p>Guarda el requerimiento para agregar observaciones.</p>
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
