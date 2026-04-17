<?php
$path = '../';
require_once '../content/auth_check.php';
require_once '../content/db.php';

// Only Admin access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
$user = [
    'username' => '',
    'full_name' => '',
    'short_name' => '',
    'role' => 'user'
];
$is_edit = false;

if ($id) {
    $is_edit = true;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch();
        if (!$user) {
            die("Usuario no encontrado");
        }
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $short_name = trim($_POST['short_name']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (empty($username) || empty($full_name) || empty($short_name)) {
        $error = "Por favor complete los campos obligatorios.";
    } else {
        try {
            if ($is_edit) {
                // Update
                $sql = "UPDATE users SET username = :username, full_name = :full_name, short_name = :short_name, role = :role";
                if (!empty($password)) {
                    $sql .= ", password = :password";
                }
                $sql .= " WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':short_name', $short_name);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':id', $id);
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt->bindParam(':password', $hashed_password);
                }
                
                if ($stmt->execute()) {
                    header("Location: users.php");
                    exit();
                }
            } else {
                // Create
                if (empty($password)) {
                    $error = "La contraseña es obligatoria para nuevos usuarios.";
                } else {
                    $sql = "INSERT INTO users (username, password, full_name, short_name, role) VALUES (:username, :password, :full_name, :short_name, :role)";
                    $stmt = $pdo->prepare($sql);
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':full_name', $full_name);
                    $stmt->bindParam(':short_name', $short_name);
                    $stmt->bindParam(':role', $role);
                    
                    if ($stmt->execute()) {
                        header("Location: users.php");
                        exit();
                    }
                }
            }
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "El nombre de usuario ya existe.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1 class="page-title"><?php echo $is_edit ? 'Editar Usuario' : 'Nuevo Usuario'; ?></h1>
    <a href="users.php" class="btn btn-sm" style="background: var(--bg-color); color: var(--text-muted);">
        <i class="ri-arrow-left-line"></i> Volver
    </a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <?php if(!empty($error)): ?>
        <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <div class="form-group">
            <label class="form-label">Nombre Completo</label>
            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Iniciales (ej: AA)</label>
            <input type="text" name="short_name" class="form-control" value="<?php echo htmlspecialchars($user['short_name']); ?>" required maxlength="10">
        </div>

        <div class="form-group">
            <label class="form-label">Nombre de Usuario</label>
            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Contraseña <?php echo $is_edit ? '(Dejar en blanco para mantener actual)' : ''; ?></label>
            <input type="password" name="password" class="form-control" <?php echo $is_edit ? '' : 'required'; ?>>
        </div>

        <div class="form-group">
            <label class="form-label">Rol</label>
            <select name="role" class="form-control">
                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Asistente (User)</option>
                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Jefe TI (Admin)</option>
            </select>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <?php echo $is_edit ? 'Actualizar Usuario' : 'Crear Usuario'; ?>
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
