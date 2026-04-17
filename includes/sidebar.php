<?php
// Function to check active page
function isActive($page)
{
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="logo">
        <i class="ri-code-s-slash-line"></i> ReqTI
    </div>
    <ul class="nav-links">
        <li class="nav-item">
            <a href="<?php echo isset($path) ? $path : ''; ?>index.php"
                class="nav-link <?php echo isActive('index.php'); ?>">
                <i class="ri-dashboard-line"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo isset($path) ? $path : ''; ?>pages/requirements.php"
                class="nav-link <?php echo isActive('pages/requirements.php') || isActive('pages/requirement_form.php') ? 'active' : ''; ?>">
                <i class="ri-task-line"></i> Requerimientos
            </a>
        </li>
        <?php if ($_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
                <a href="<?php echo isset($path) ? $path : ''; ?>pages/users.php"
                    class="nav-link <?php echo isActive('pages/users.php') || isActive('pages/user_form.php') ? 'active' : ''; ?>">
                    <i class="ri-user-settings-line"></i> Usuarios
                </a>
            </li>
        <?php endif; ?>

        <li class="nav-item-header"
            style="padding: 1rem 1.5rem 0.5rem; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">
            Inventario
        </li>
        <li class="nav-item">
            <a href="<?php echo isset($path) ? $path : ''; ?>pages/stock_tintas.php"
                class="nav-link <?php echo isActive('pages/stock_tintas.php') ? 'active' : ''; ?>">
                <i class="ri-drop-line"></i> Stock de Tintas
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo isset($path) ? $path : ''; ?>pages/agregar_tinta.php"
                class="nav-link <?php echo isActive('pages/agregar_tinta.php') ? 'active' : ''; ?>">
                <i class="ri-add-circle-line"></i> Agregar Tinta
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo isset($path) ? $path : ''; ?>pages/salida_tinta.php"
                class="nav-link <?php echo isActive('pages/salida_tinta.php') ? 'active' : ''; ?>">
                <i class="ri-logout-circle-line"></i> Salida de Tinta
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo isset($path) ? $path : ''; ?>logout.php" class="nav-link">
                <i class="ri-logout-box-r-line"></i> Cerrar Sesión
            </a>
        </li>
    </ul>

    <div style="position: absolute; bottom: 2rem; color: var(--text-muted); font-size: 0.875rem;">
        <i class="ri-user-smile-line"></i> Hola, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
    </div>
</aside>
<main class="main-content">