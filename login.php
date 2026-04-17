<?php
// Asegúrate de que la ruta sea correcta. En tu archivo anterior era 'db.php', aquí pusiste 'content/db.php'.
// Ajusta según tu estructura de carpetas.
if (file_exists('db.php')) {
    require_once 'db.php';
} elseif (file_exists('content/db.php')) {
    require_once 'content/db.php';
} else {
    die("Error: No se encuentra el archivo de conexión a la base de datos.");
}

session_start();

// Si ya está logueado, redirigir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Por favor complete todos los campos.";
    } else {
        $sql = "SELECT id, username, password, full_name, short_name, role FROM users WHERE username = :username";
        
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row['id'];
                        $hashed_password = $row['password'];
                        
                        // Verificación de contraseña
                        if (password_verify($password, $hashed_password)) {
                            // Contraseña correcta, iniciar sesión
                            $_SESSION['user_id'] = $id;
                            $_SESSION['username'] = $row['username'];
                            $_SESSION['full_name'] = $row['full_name'];
                            $_SESSION['short_name'] = $row['short_name'];
                            $_SESSION['role'] = $row['role'];
                            
                            header("Location: index.php");
                            exit();
                        }
                    }
                }
                // SEGURIDAD: Usar mensaje genérico para no revelar si el usuario existe o no
                $error = "Usuario o contraseña incorrectos.";
            } else {
                $error = "Error de sistema. Intente más tarde.";
            }
            unset($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RqTI</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Remix Icons (Mismo set que usas en el sistema) -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .auth-card {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .auth-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 0.5rem 0 0 0;
        }

        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            color: var(--text-muted);
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem; /* Espacio para el icono */
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            cursor: pointer;
            color: var(--text-muted);
            background: none;
            border: none;
            padding: 0;
        }
        
        .toggle-password:hover {
            color: var(--text-main);
        }

        .btn-primary {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-text {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="ri-server-line"></i> Requerimientos TI
            </div>
            <h1 class="auth-title">Bienvenido de nuevo</h1>
            <p style="color: var(--text-muted); margin: 0.5rem 0 0; font-size: 0.875rem;">Ingrese sus credenciales para continuar</p>
        </div>

        <form action="" method="post">
            <?php if(!empty($error)): ?>
                <div class="alert-error">
                    <i class="ri-error-warning-line"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">Usuario</label>
                <div class="input-wrapper">
                    <i class="ri-user-line input-icon"></i>
                    <input type="text" name="username" class="form-control" required 
                           placeholder="ej. jdoe" 
                           autocomplete="username" 
                           autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <div class="input-wrapper">
                    <i class="ri-lock-password-line input-icon"></i>
                    <input type="password" name="password" id="passwordInput" class="form-control" required 
                           placeholder="••••••••" 
                           autocomplete="current-password">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="ri-eye-line" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                Ingresar <i class="ri-arrow-right-line"></i>
            </button>
        </form>

        <div class="footer-text">
            Sistema de Gestión de Requerimientos TI<br>v1.0 / 2025 VSC
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            } else {
                input.type = "password";
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            }
        }
    </script>
</body>
</html>