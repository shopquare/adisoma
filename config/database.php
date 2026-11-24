<?php
$is_local = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'])
    || str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:')
    || str_starts_with($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1:')
    || (php_sapi_name() === 'cli' && getenv('APP_ENV') !== 'production');

if ($is_local) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'adisoma');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('BASE_URL', '/adisoma');
} else {
    define('DB_HOST', 'sql213.infinityfree.com');
    define('DB_NAME', 'if0_43325536_adisoma');
    define('DB_USER', 'if0_43325536');
    define('DB_PASS', 'cLqnZJLlBJMVl9');
    define('BASE_URL', '');
}
const CO=['name'=>'ADISOMA Solution','address'=>'Mumbai, Maharashtra','phone'=>'+91 98765 43210','email'=>'info@adisomasolution.com',
 'facebook'=>'#','linkedin'=>'#','twitter'=>'#','instagram'=>'#'];
session_start();
if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(16));
function db():PDO{static $p;if(!$p){$p=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,
 [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}return $p;}