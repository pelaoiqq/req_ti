<?php
$path = '../';
require_once '../content/auth_check.php';
require_once '../content/db.php';
include '../includes/header.php';
include '../includes/sidebar.php';

// Filter Logic
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$responsible_filter = isset($_GET['responsible']) ? $_GET['responsible'] : '';

?>

<div class="page-header">
    <h1 class="page-title">Requerimientos</h1>
    <?php if($_SESSION['role'] == 'admin'): ?>
    <a href="requirement_form.php" class="btn btn-primary btn-sm">
        <i class="ri-add-line" style="margin-right: 0.5rem;"></i> Nuevo Requerimiento
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <form method="GET" action="" class="d-flex gap-2 mb-4" style="flex-wrap: wrap;">
        <div class="form-group" style="margin-bottom: 0;">
            <select name="status" class="form-control" style="width: auto;" onchange="this.form.submit()">
                <option value="">Todos los Estados</option>
                <option value="Abierto" <?php echo $status_filter == 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                <option value="Cerrado" <?php echo $status_filter == 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
             <select name="responsible" class="form-control" style="width: auto;" onchange="this.form.submit()">
                <option value="">Todos los Responsables</option>
                <?php
                $user_stmt = $pdo->query("SELECT id, short_name FROM users ORDER BY short_name ASC");
                while($u = $user_stmt->fetch()) {
                    $selected = $responsible_filter == $u['id'] ? 'selected' : '';
                    echo "<option value='".$u['id']."' $selected>".$u['short_name']."</option>";
                }
                ?>
            </select>
        </div>
        <a href="requirements.php" class="btn btn-sm" style="background: var(--bg-color); color: var(--text-muted); display: flex; align-items: center;">Limpiar</a>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Colegio</th>
                    <th>Descripción</th>
                    <th>Responsable</th>
                    <th>Inicio / Fin</th>
                    <th>Alerta</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql = "SELECT r.*, u.short_name as responsable_name 
                           FROM requirements r 
                           JOIN users u ON r.responsible_id = u.id 
                           WHERE 1=1";
                    
                    if (!empty($status_filter)) {
                        $sql .= " AND r.status = :status";
                    }
                    if (!empty($responsible_filter)) {
                        $sql .= " AND r.responsible_id = :responsible";
                    }
                    
                    $sql .= " ORDER BY r.created_at DESC";
                    
                    $stmt = $pdo->prepare($sql);
                    
                    if (!empty($status_filter)) {
                        $stmt->bindParam(':status', $status_filter);
                    }
                    if (!empty($responsible_filter)) {
                        $stmt->bindParam(':responsible', $responsible_filter);
                    }
                    
                    $stmt->execute();
                    
                    while($row = $stmt->fetch()) {
                        $status_badge = $row['status'] == 'Abierto' ? 'badge-warning' : 'badge-success';
                        
                        $priority_badge = '';
                        if($row['priority'] == 'Critica') $priority_badge = 'badge-danger';
                        elseif($row['priority'] == 'Alta') $priority_badge = 'badge-warning';
                        else $priority_badge = 'badge-primary';
                        
                        $st_date_obj = new DateTime($row['start_date']);
                        $st_date_obj->setTime(0,0,0);
                        $tod_obj = new DateTime();
                        $tod_obj->setTime(0,0,0);
                        $d_allow = 0;
                        if ($row['priority'] == 'Normal') $d_allow = 5;
                        elseif ($row['priority'] == 'Alta') $d_allow = 2;
                        elseif ($row['priority'] == 'Critica') $d_allow = 0;
                        $dl_obj = clone $st_date_obj;
                        $dl_obj->modify("+$d_allow days");
                        $diff_obj = $tod_obj->diff($dl_obj);
                        $d_rem = (int)$diff_obj->format('%R%a');
                        
                        $alert_badge = "-";
                        if ($row['status'] == 'Abierto') {
                            if ($d_rem < 0) {
                                $alert_badge = "<span class='badge badge-danger' title='Vencido hace " . abs($d_rem) . " días'><i class='ri-alert-line'></i> Vencido</span>";
                            } elseif ($d_rem == 0) {
                                $alert_badge = "<span class='badge badge-warning' title='Vence hoy' style='background: #f59e0b; color: white;'><i class='ri-timer-line'></i> Hoy</span>";
                            } elseif ($d_rem == 1) {
                                $alert_badge = "<span class='badge' style='background: #fef08a; color: #854d0e;' title='Vence mañana'><i class='ri-timer-line'></i> Mañana</span>";
                            } else {
                                $alert_badge = "<span class='badge badge-success' title='En tiempo, faltan " . $d_rem . " días'><i class='ri-check-line'></i> " . $d_rem . " D</span>";
                            }
                        }

                        $end_date_display = $row['end_date'] ? date('d-m-Y', strtotime($row['end_date'])) : '-';

                        echo "<tr>";
                        echo "<td>#" . $row['id'] . "</td>";
                        echo "<td><span class='badge' style='background: #e5e7eb; color: #374151;'>" . htmlspecialchars($row['school']) . "</span></td>";
                        echo "<td>" . htmlspecialchars(substr($row['description'], 0, 40)) . (strlen($row['description']) > 40 ? '...' : '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['responsable_name']) . "</td>";
                        echo "<td>
                                <div style='font-size: 0.85rem;'>
                                    <div>In: " . date('d-m-Y', strtotime($row['start_date'])) . "</div>
                                    <div style='color: var(--text-muted);'>Fi: " . $end_date_display . "</div>
                                </div>
                              </td>";
                        echo "<td>" . $alert_badge . "</td>";
                        echo "<td><span class='badge " . $status_badge . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                         echo "<td><span class='badge " . $priority_badge . "'>" . htmlspecialchars($row['priority']) . "</span></td>";
                        echo "<td>
                                <a href='requirement_form.php?id=" . $row['id'] . "' class='btn btn-sm' style='background: #eff6ff; color: #1d4ed8;'>
                                    <i class='ri-eye-line'></i> Ver
                                </a>
                              </td>";
                        echo "</tr>";
                    }
                } catch(PDOException $e) {
                    echo "<tr><td colspan='8'>Error cargando datos.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
