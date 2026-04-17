<?php
$path = '';
require_once 'content/auth_check.php';
require_once 'content/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Fetch stats
$dashboard_stats = [
    'total' => 0,
    'open' => 0,
    'closed' => 0
];

try {
    // Total
    $stmt = $pdo->query("SELECT COUNT(*) FROM requirements");
    $dashboard_stats['total'] = $stmt->fetchColumn();

    // Open
    $stmt = $pdo->query("SELECT COUNT(*) FROM requirements WHERE status = 'Abierto'");
    $dashboard_stats['open'] = $stmt->fetchColumn();

    // Closed
    $stmt = $pdo->query("SELECT COUNT(*) FROM requirements WHERE status = 'Cerrado'");
    $dashboard_stats['closed'] = $stmt->fetchColumn();

    // Critical Inks
    $stmt = $pdo->query("SELECT colegio, model, color, total_quantity FROM inks WHERE total_quantity <= 5 ORDER BY colegio ASC, model ASC, color ASC");
    $critical_inks = $stmt->fetchAll();

    // Requerimientos con Alerta
    $stmt = $pdo->query("SELECT r.*, u.short_name as responsable_name FROM requirements r JOIN users u ON r.responsible_id = u.id WHERE r.status = 'Abierto'");
    $open_reqs = $stmt->fetchAll();
    
    $delayed_reqs = [];
    $due_today_reqs = [];
    
    $today_time_obj = new DateTime();
    $today_time_obj->setTime(0,0,0);
    
    foreach($open_reqs as $req) {
        $st_date = new DateTime($req['start_date']);
        $st_date->setTime(0,0,0);
        $d_allowed = 0;
        if ($req['priority'] == 'Normal') $d_allowed = 5;
        if ($req['priority'] == 'Alta') $d_allowed = 2;
        if ($req['priority'] == 'Critica') $d_allowed = 0;
        
        $dl_date = clone $st_date;
        $dl_date->modify("+$d_allowed days");
        
        $diff = $today_time_obj->diff($dl_date);
        $rem = (int)$diff->format('%R%a');
        
        if ($rem < 0) {
            $req['days_overdue'] = abs($rem);
            $delayed_reqs[] = $req;
        } elseif ($rem == 0) {
            $due_today_reqs[] = $req;
        }
    }

} catch(PDOException $e) {
    // Handle error silently or log
    $critical_inks = [];
    $delayed_reqs = [];
    $due_today_reqs = [];
}
?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <?php if($_SESSION['role'] == 'admin'): ?>
    <a href="pages/requirement_form.php" class="btn btn-primary btn-sm">
        <i class="ri-add-line" style="margin-right: 0.5rem;"></i> Nuevo Requerimiento
    </a>
    <?php endif; ?>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-title">Total Requerimientos</span>
        <span class="stat-value"><?php echo $dashboard_stats['total']; ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-title">Requerimientos Abiertos</span>
        <span class="stat-value" style="color: var(--warning);"><?php echo $dashboard_stats['open']; ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-title">Requerimientos Cerrados</span>
        <span class="stat-value" style="color: var(--success);"><?php echo $dashboard_stats['closed']; ?></span>
    </div>
</div>

<?php if(isset($critical_inks) && count($critical_inks) > 0): ?>
<div class="card" style="border-left: 4px solid var(--danger); margin-bottom: 2rem;">
    <div class="d-flex justify-between align-center mb-4">
        <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--danger);">
            <i class="ri-alert-fill" style="margin-right: 0.5rem;"></i> Alerta de Stock Crítico de Tintas
        </h3>
        <a href="pages/stock_tintas.php" class="btn btn-sm" style="background: var(--bg-color); color: var(--text-muted);">Ver inventario</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Colegio</th>
                    <th>Modelo</th>
                    <th>Color</th>
                    <th>Stock Actual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($critical_inks as $ink): ?>
                <tr>
                    <td><span class='badge' style='background: #e5e7eb; color: #374151;'><?php echo htmlspecialchars($ink['colegio'] ?? 'DP'); ?></span></td>
                    <td><?php echo htmlspecialchars($ink['model']); ?></td>
                    <td>
                        <?php
                        $colorHex = '#e5e7eb';
                        $colorText = '#374151';
                        $c = strtolower($ink['color']);
                        if(strpos($c, 'cyan') !== false || strpos($c, 'cian') !== false) {
                            $colorHex = '#00FFFF'; $colorText = '#000';
                        } elseif(strpos($c, 'magenta') !== false) {
                            $colorHex = '#FF00FF'; $colorText = '#fff';
                        } elseif(strpos($c, 'yellow') !== false || strpos($c, 'amarillo') !== false) {
                            $colorHex = '#FFFF00'; $colorText = '#000';
                        } elseif(strpos($c, 'black') !== false || strpos($c, 'negro') !== false) {
                            $colorHex = '#000000'; $colorText = '#fff';
                        }
                        echo "<span class='badge' style='background: {$colorHex}; color: {$colorText}; border: 1px solid #ccc;'>" . htmlspecialchars($ink['color']) . "</span>";
                        ?>
                    </td>
                    <td><span class="badge badge-danger"><?php echo htmlspecialchars($ink['total_quantity']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if((isset($delayed_reqs) && count($delayed_reqs) > 0) || (isset($due_today_reqs) && count($due_today_reqs) > 0)): ?>
<div class="card" style="border-left: 4px solid var(--danger); margin-bottom: 2rem;">
    <div class="d-flex justify-between align-center mb-4">
        <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--danger);">
            <i class="ri-alarm-warning-line" style="margin-right: 0.5rem;"></i> Alerta de Tiempos de Requerimientos
        </h3>
        <a href="pages/requirements.php" class="btn btn-sm" style="background: var(--bg-color); color: var(--text-muted);">Ver requerimientos</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Colegio</th>
                    <th>Prioridad</th>
                    <th>Responsable</th>
                    <th>Estado de Alerta</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($delayed_reqs as $req): ?>
                <tr>
                    <td><span class='badge' style='background: #e5e7eb; color: #374151;'><?php echo htmlspecialchars($req['school']); ?></span></td>
                    <td><span class="badge <?php echo $req['priority'] == 'Critica' ? 'badge-danger' : ($req['priority'] == 'Alta' ? 'badge-warning' : 'badge-primary'); ?>"><?php echo htmlspecialchars($req['priority']); ?></span></td>
                    <td><?php echo htmlspecialchars($req['responsable_name']); ?></td>
                    <td><span class="badge badge-danger"><i class="ri-alert-line"></i> Vencido hace <?php echo $req['days_overdue']; ?> días</span></td>
                </tr>
                <?php endforeach; ?>
                <?php foreach($due_today_reqs as $req): ?>
                <tr>
                    <td><span class='badge' style='background: #e5e7eb; color: #374151;'><?php echo htmlspecialchars($req['school']); ?></span></td>
                    <td><span class="badge <?php echo $req['priority'] == 'Critica' ? 'badge-danger' : ($req['priority'] == 'Alta' ? 'badge-warning' : 'badge-primary'); ?>"><?php echo htmlspecialchars($req['priority']); ?></span></td>
                    <td><?php echo htmlspecialchars($req['responsable_name']); ?></td>
                    <td><span class="badge badge-warning" style="background: #f59e0b; color: white;"><i class="ri-timer-line"></i> Vence Hoy</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="d-flex justify-between align-center mb-4">
        <h3 style="font-size: 1.25rem; font-weight: 600;">Requerimientos Recientes</h3>
        <a href="pages/requirements.php" class="btn btn-sm" style="background: var(--bg-color); color: var(--text-muted);">Ver todos</a>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Colegio</th>
                    <th>Descripción</th>
                    <th>Responsable</th>
                    <th>Fecha Inicio</th>
                    <th>Alerta</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql = "SELECT r.*, u.short_name as responsable_name 
                           FROM requirements r 
                           JOIN users u ON r.responsible_id = u.id 
                           ORDER BY r.created_at DESC LIMIT 5";
                    $stmt = $pdo->query($sql);
                    
                    if ($stmt->rowCount() > 0) {
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
                            
                            echo "<tr>";
                            echo "<td><span class='badge' style='background: #e5e7eb; color: #374151;'>" . htmlspecialchars($row['school']) . "</span></td>";
                            echo "<td>" . htmlspecialchars(substr($row['description'], 0, 50)) . (strlen($row['description']) > 50 ? '...' : '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['responsable_name']) . "</td>";
                            echo "<td>" . date('d-m-Y', strtotime($row['start_date'])) . "</td>";
                            echo "<td>" . $alert_badge . "</td>";
                            echo "<td><span class='badge " . $priority_badge . "'>" . htmlspecialchars($row['priority']) . "</span></td>";
                            echo "<td><span class='badge " . $status_badge . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-muted' style='text-align: center; padding: 2rem;'>No hay requerimientos registrados.</td></tr>";
                    }
                } catch(PDOException $e) {
                    echo "<tr><td colspan='6'>Error cargando datos.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
