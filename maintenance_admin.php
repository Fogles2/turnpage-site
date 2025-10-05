<?php
session_start();
require_once 'config.php';

function read_state() {
    $file = MAINTENANCE_STATE_FILE;
    $default = ['on' => false, 'target' => date('c', strtotime('+7 days'))];
    $json = json_decode(@file_get_contents($file), true);
    if (!is_array($json)) $json = ['on' => false, 'target' => date('c', strtotime('+7 days'))];
    return $json;
}

function write_state($data){
    $file = MAINTENANCE_STATE_FILE;
    @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
}

// Login
if (!isset($_SESSION['tp_admin'])) {
    if (isset($_POST['password'])) {
        if (hash_equals(MAINTENANCE_PASSWORD, (string)$_POST['password'])) {
            $_SESSION['tp_admin'] = true;
            header('Location: maintenance_admin.php');
            exit;
        }
        $error = 'Invalid password';
    }
    echo '<!DOCTYPE html>
<html>
<head><title>Turnpage Admin</title>
<style>body{font-family:system-ui;max-width:400px;margin:50px auto;padding:20px}input,button{padding:10px;margin:5px 0;width:100%;box-sizing:border-box}.error{color:red}</style>
</head>
<body>
<h2>Turnpage Admin</h2>
<form method="post">
<input type="password" name="password" placeholder="Admin Password" required>
<button type="submit">Login</button>
</form>
' . (isset($error) ? '<p class="error">' . htmlspecialchars($error) . '</p>' : '') . '
</body></html>';
    exit;
}

// Handle actions
if (isset($_POST['action'])) {
    $state = read_state();
    
    if ($_POST['action'] === 'toggle') {
        $state['on'] = !$state['on'];
        write_state($state);
    } elseif ($_POST['action'] === 'set_target' && isset($_POST['target'])) {
        $state['target'] = $_POST['target'];
        write_state($state);
    } elseif ($_POST['action'] === 'logout') {
        session_destroy();
        header('Location: maintenance_admin.php');
        exit;
    }
    
    header('Location: maintenance_admin.php');
    exit;
}

$state = read_state();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Turnpage Admin</title>
    <style>
        body { font-family: system-ui; max-width: 600px; margin: 50px auto; padding: 20px; }
        .status { padding: 15px; border-radius: 5px; margin: 20px 0; }
        .on { background: #ffe6e6; color: #cc0000; }
        .off { background: #e6ffe6; color: #006600; }
        button { padding: 10px 20px; margin: 5px; }
        input[type="datetime-local"] { padding: 8px; }
        .actions { margin: 20px 0; }
        a { color: #0066cc; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Turnpage Admin</h1>
    
    <div class="status <?php echo $state['on'] ? 'on' : 'off'; ?>">
        <strong>Maintenance Mode: <?php echo $state['on'] ? 'ON' : 'OFF'; ?></strong>
        <?php if ($state['on']): ?>
            <br>Target completion: <?php echo htmlspecialchars($state['target']); ?>
        <?php endif; ?>
    </div>
    
    <div class="actions">
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="toggle">
            <button type="submit"><?php echo $state['on'] ? 'Turn OFF' : 'Turn ON'; ?> Maintenance</button>
        </form>
        
        <form method="post" style="margin-top: 20px;">
            <label>Target completion time:</label><br>
            <input type="datetime-local" name="target" value="<?php echo date('Y-m-d\TH:i', strtotime($state['target'])); ?>" required>
            <input type="hidden" name="action" value="set_target">
            <button type="submit">Update Target</button>
        </form>
        
        <form method="post" style="margin-top: 20px;">
            <input type="hidden" name="action" value="logout">
            <button type="submit">Logout</button>
        </form>
    </div>
    
    <hr>
    <p><a href="maintenance.php">View maintenance page</a> | <a href="index.php">View main site</a> | <a href="index.php?bypass=1">Bypass maintenance</a></p>
    
    <h3>Instructions</h3>
    <p>Remember to change the admin password in <code>config.php</code> and configure SMTP for PHPMailer.</p>
</body>
</html>