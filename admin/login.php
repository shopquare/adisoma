<?php require_once __DIR__.'/../includes/functions.php';
$setup = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0;
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $err = 'Invalid session token. Please reload and retry.';
    } else {
        $u = post('username');
        $p = $_POST['password'] ?? '';
        
        $maxAttempts = 5;
        $lockoutSeconds = 900; // 15 minutes
        $now = time();

        if (isset($_SESSION['lockout_time']) && $_SESSION['lockout_time'] > $now) {
            $remaining = ceil(($_SESSION['lockout_time'] - $now) / 60);
            $err = "Too many failed login attempts. Security lockout active. Please retry in {$remaining} minute(s).";
        } elseif ($setup) {
            if ($u === '' || strlen($p) < 8) {
                $err = 'Username is required and password must be at least 8 characters.';
            } else {
                db()->prepare('INSERT INTO admins(username,password) VALUES(?,?)')
                    ->execute([$u, password_hash($p, PASSWORD_DEFAULT)]);
                flash('Admin created successfully! Please log in.');
                redirect('login.php');
            }
        } else {
            $s = db()->prepare('SELECT * FROM admins WHERE username = ?');
            $s->execute([$u]);
            $a = $s->fetch();
            usleep(250000); // Mitigate timing analysis
            
            if ($a && password_verify($p, $a['password'])) {
                unset($_SESSION['failed_attempts'], $_SESSION['lockout_time']);
                session_regenerate_id(true);
                $_SESSION['admin'] = $a['id'];
                log_action('Logged In', 'Admin', $a['id'], "User {$a['username']} authenticated");
                redirect('dashboard.php');
            } else {
                $attempts = ($_SESSION['failed_attempts'] ?? 0) + 1;
                $_SESSION['failed_attempts'] = $attempts;
                if ($attempts >= $maxAttempts) {
                    $_SESSION['lockout_time'] = $now + $lockoutSeconds;
                    $err = 'Too many failed login attempts. Access is locked for 15 minutes.';
                } else {
                    $left = $maxAttempts - $attempts;
                    $err = "Invalid username or password. ({$left} attempt" . ($left === 1 ? '' : 's') . " remaining)";
                }
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $setup ? 'Create Admin Account' : 'Admin Login' ?> | ADISOMA Solution</title>
    <link rel="stylesheet" href="<?=BASE_URL?>/assets/css/style.css?v=<?=asset_v()?>">
    <link rel="stylesheet" href="<?=BASE_URL?>/assets/css/responsive.css?v=<?=asset_v()?>">
    <style>
        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            background: #f1f5f9;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 32px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .login-card .logo-wrap {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-card img {
            max-height: 75px;
            object-fit: contain;
        }
        .login-card h2 {
            font-size: 24px;
            color: #0f172a;
            margin: 0 0 8px;
            text-align: center;
            font-weight: 700;
        }
        .login-card p.sub {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin: 0 0 25px;
        }
        .login-card label {
            display: block;
            margin: 18px 0 8px;
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }
        .login-card input {
            width: 100%;
            padding: 13px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            background: #f8fafc;
            color: #0f172a;
            font-family: inherit;
            transition: all 0.25s;
        }
        .login-card input:focus {
            border-color: var(--o);
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.15);
        }
        .login-card .btn {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            font-size: 16px;
            border-radius: 12px;
        }
        .login-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="logo-wrap">
            <a href="<?=BASE_URL?>/index.php">
                <img src="<?=BASE_URL?>/assets/images/adisoma-logo.png" alt="ADISOMA Solution">
            </a>
        </div>
        <h2><?= $setup ? 'Create Admin Account' : 'Admin Login' ?></h2>
        <p class="sub"><?= $setup ? 'Set up the initial administrator account' : 'Enter your credentials to access dashboard' ?></p>
        
        <?= flash() ?>
        <?php if($err): ?>
            <div class="alert err" style="margin-bottom:20px;"><?= e($err) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrf() ?>
            <label>Username</label>
            <input name="username" required placeholder="Enter username" autocomplete="username">
            
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
            
            <button class="btn" type="submit"><?= $setup ? 'Create Account' : 'Login to Dashboard ➔' ?></button>
        </form>

        <div class="login-footer">
            <a href="<?=BASE_URL?>/index.php" style="color:#64748b; font-weight:500;">← Back to Website</a>
        </div>
    </div>
</div>
</body>
</html>
