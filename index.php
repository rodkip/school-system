<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php'); // $dbh PDO instance

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Attempt to resolve MAC address from ARP table.
 * Only works if server and client are on same L2 network/subnet.
 */
function getClientMac($ipAddress) {
    $macAddress = null;
    // Restrict to common private subnets optionally (reduce command abuse)
    if (empty($ipAddress)) return null;

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $arp = @shell_exec("arp -a " . escapeshellarg($ipAddress) . " 2>&1");
    } else {
        $arp = @shell_exec("arp -n " . escapeshellarg($ipAddress) . " 2>&1");
    }

    if ($arp && preg_match('/(([a-f0-9]{2}[:-]){5}[a-f0-9]{2})/i', $arp, $matches)) {
        $macAddress = strtolower($matches[0]);
    }

    return $macAddress;
}

/**
 * Log login event into database with audit hash.
 * $data is associative array with keys matching column bind names.
 */
function logLoginEvent(PDO $dbh, array $data) {
    // canonical order for audit hashing
    $canonicalFields = [
        'username','login_time','ip_address','forwarded_ip','mac_address','geo_location',
        'user_agent','device_fingerprint','session_id','csrf_token_used','login_status',
        'failure_reason','twofa_method','tls_enabled','suspicious_flag'
    ];

    // Build audit string in canonical order (use empty string when missing)
    $parts = [];
    foreach ($canonicalFields as $f) {
        // if login_time is provided as NOW() on DB side, use provided value or blank
        $parts[] = isset($data[$f]) ? (string)$data[$f] : '';
    }
    $auditString = implode('|', $parts);
    $auditHash = hash('sha256', $auditString);

    $sql = "INSERT INTO login_activity
        (username, login_time, ip_address, forwarded_ip, mac_address, geo_location,
         user_agent, device_fingerprint, session_id, csrf_token_used, login_status,
         failure_reason, twofa_method, tls_enabled, suspicious_flag, audit_hash)
        VALUES
        (:username, NOW(), :ip_address, :forwarded_ip, :mac_address, :geo_location,
         :user_agent, :device_fingerprint, :session_id, :csrf_token_used, :login_status,
         :failure_reason, :twofa_method, :tls_enabled, :suspicious_flag, :audit_hash)";

    $stmt = $dbh->prepare($sql);

    // Bind required and optional values
    $stmt->bindValue(':username',           $data['username']           ?? '');
    $stmt->bindValue(':ip_address',         $data['ip_address']         ?? ($_SERVER['REMOTE_ADDR'] ?? ''));
    $stmt->bindValue(':forwarded_ip',       $data['forwarded_ip']       ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? null));
    $stmt->bindValue(':mac_address',        $data['mac_address']        ?? null);
    $stmt->bindValue(':geo_location',       $data['geo_location']       ?? null);
    $stmt->bindValue(':user_agent',         $data['user_agent']         ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));
    $stmt->bindValue(':device_fingerprint', $data['device_fingerprint'] ?? null);
    $stmt->bindValue(':session_id',         $data['session_id']         ?? session_id());
    $stmt->bindValue(':csrf_token_used',    $data['csrf_token_used']    ?? null);
    $stmt->bindValue(':login_status',       $data['login_status']       ?? 'Failed');
    $stmt->bindValue(':failure_reason',     $data['failure_reason']     ?? null);
    $stmt->bindValue(':twofa_method',       $data['twofa_method']       ?? 'None');
    $stmt->bindValue(':tls_enabled',        $data['tls_enabled']        ?? ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 1 : 0 ), PDO::PARAM_INT);
    $stmt->bindValue(':suspicious_flag',    $data['suspicious_flag']    ?? 0, PDO::PARAM_INT);
    $stmt->bindValue(':audit_hash',         $auditHash);

    $stmt->execute();
}

/**
 * Helper: sanitize username input
 */
function getPostedUsername() {
    return filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

/**
 * Device fingerprint server fallback - prefer client-sent fingerprint if provided.
 * Client code will send a deterministic fingerprint (sha256 hex).
 */
function getDeviceFingerprint() {
    if (!empty($_POST['device_fingerprint'])) {
        return preg_replace('/[^a-f0-9]/i', '', $_POST['device_fingerprint']);
    }
    // fallback: server-side light fingerprint (less robust)
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    return substr(hash('sha256', $ua . '|' . $lang), 0, 64);
}

/* ------------------ LOGIN PROCESS ------------------ */

if (isset($_POST['login'])) {
    $username    = getPostedUsername();
    $password    = $_POST['password'] ?? '';
    $csrfPost    = $_POST['csrf_token'] ?? '';
    $ipAddress   = $_SERVER['REMOTE_ADDR'] ?? '';
    $forwardedIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
    $macAddress  = getClientMac($ipAddress);
    $userAgent   = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $deviceFp    = getDeviceFingerprint();
    $tlsEnabled  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 1 : 0;
    $sessionID   = session_id();
    $geoLocation = null; // optional: enrich with GeoIP service (offline DB or API)
    $twofaMethod = 'None';

    // Validate input
    if (empty($username)) {
        echo "<script>alert('Please enter your username.');</script>";
        exit;
    }

    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], (string)$csrfPost)) {
        // Log CSRF mismatch as suspicious
        logLoginEvent($dbh, [
            'username' => $username,
            'ip_address' => $ipAddress,
            'forwarded_ip' => $forwardedIP,
            'mac_address' => $macAddress,
            'geo_location' => $geoLocation,
            'user_agent' => $userAgent,
            'device_fingerprint' => $deviceFp,
            'session_id' => $sessionID,
            'csrf_token_used' => $csrfPost,
            'login_status' => 'Failed',
            'failure_reason' => 'CSRF Mismatch',
            'twofa_method' => 'None',
            'tls_enabled' => $tlsEnabled,
            'suspicious_flag' => 1
        ]);
        echo "<script>alert('Security check failed. Please refresh and try again.');</script>";
        exit;
    }

    // Lookup user
    $sql = "SELECT ID, password, emailaddress, fullnames, username, mobilenumber 
            FROM tbladmin WHERE username = :username LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        // No user found -> log and return generic message
        logLoginEvent($dbh, [
            'username' => $username,
            'ip_address' => $ipAddress,
            'forwarded_ip' => $forwardedIP,
            'mac_address' => $macAddress,
            'geo_location' => $geoLocation,
            'user_agent' => $userAgent,
            'device_fingerprint' => $deviceFp,
            'session_id' => $sessionID,
            'csrf_token_used' => $csrfPost,
            'login_status' => 'Failed',
            'failure_reason' => 'User Not Found',
            'twofa_method' => 'None',
            'tls_enabled' => $tlsEnabled,
            'suspicious_flag' => 0
        ]);
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Invalid Login Details. Re-enter";
        // Intentionally not revealing whether username exists
        header("Location: index.php");
        exit;
    }

    $hashedPassword = $result['password'];
    if (!password_verify($password, $hashedPassword)) {
        // Invalid password
        logLoginEvent($dbh, [
            'username' => $username,
            'ip_address' => $ipAddress,
            'forwarded_ip' => $forwardedIP,
            'mac_address' => $macAddress,
            'geo_location' => $geoLocation,
            'user_agent' => $userAgent,
            'device_fingerprint' => $deviceFp,
            'session_id' => $sessionID,
            'csrf_token_used' => $csrfPost,
            'login_status' => 'Failed',
            'failure_reason' => 'Invalid Password',
            'twofa_method' => 'None',
            'tls_enabled' => $tlsEnabled,
            'suspicious_flag' => 0
        ]);
        $_SESSION['messagestate'] = 'deleted';
        $_SESSION['mess'] = "Invalid Login Details. Re-enter credentials again";
        header("Location: index.php");
        exit;
    }

    // Password OK -> set session and log success
    $_SESSION['cpmsaid'] = $result['ID'];
    $_SESSION['login']   = $username;
    $_SESSION['timer']   = time();

    // Update admin online status
    $updateStatusSql = "UPDATE tbladmin SET status = 'online', last_active = NOW() WHERE username = :username";
    $updateStatusQuery = $dbh->prepare($updateStatusSql);
    $updateStatusQuery->bindParam(':username', $username, PDO::PARAM_STR);
    $updateStatusQuery->execute();

    // 2FA generation (Email / SMS) - consult notificationssettings
    $loginCode = random_int(100000, 999999);
    $_SESSION['loginCode'] = $loginCode;
    $_SESSION['loginCodeExpires'] = time() + 900; // 15 minutes

    $emailaddress = $result['emailaddress'];
    $fullnames = $result['fullnames'];
    $mobilenumber = $result['mobilenumber'];
    $mobilenumber = '254' . substr(preg_replace('/\D/', '', $mobilenumber), -9);

    date_default_timezone_set('Africa/Nairobi');

    $emailNotifySql = 'SELECT * FROM notificationssettings WHERE notificationname = "EmailLogin Notification" AND notificationstatus = "Active"';
    $smsNotifySql   = 'SELECT * FROM notificationssettings WHERE notificationname = "SMSLogin Notification" AND notificationstatus = "Active"';
    $emailStmt = $dbh->prepare($emailNotifySql);
    $smsStmt   = $dbh->prepare($smsNotifySql);
    $emailStmt->execute();
    $smsStmt->execute();

    $emailActive = $emailStmt->rowCount() > 0;
    $smsActive   = $smsStmt->rowCount() > 0;

    $codeSent = false;
    if ($emailActive) {
        $subject = 'Login Notification - Login CODE';
        $emailMessage = "
            <html>
            <head><title>$subject</title></head>
            <body style=\"font-family: Consolas, monospace;\">
                <p>Hello $fullnames,</p>
                <p>Your login code is: <b>$loginCode</b></p>
                <p>This code will expire in 15 minutes.</p>
                <p>If you did not request this code, please contact the system administrator immediately.</p>
                <p>Best regards,<br>Database Administrator</p>
                <p><i><b>Going the extra mile.</b></i></p>
            </body>
            </html>";
        $message = $emailMessage; // emailsettings.php should expect $subject and $message
        include('includes/emailsettings.php'); // handle mail sending
        $codeSent = true;
        $twofaMethod = 'Email';
    }

    if (!$codeSent && $smsActive) {
        $smsMessage = "Hi $fullnames,\nCode: $loginCode. Valid: 15 mins\nNot you? Call admin.\nGoing the extra mile.";
        $message = $smsMessage;
        include('includes/smssettings.php'); // handle SMS sending
        $codeSent = true;
        $twofaMethod = 'SMS';
    }

    // Log successful login including chosen 2fa method
    logLoginEvent($dbh, [
        'username' => $username,
        'ip_address' => $ipAddress,
        'forwarded_ip' => $forwardedIP,
        'mac_address' => $macAddress,
        'geo_location' => $geoLocation,
        'user_agent' => $userAgent,
        'device_fingerprint' => $deviceFp,
        'session_id' => $sessionID,
        'csrf_token_used' => $csrfPost,
        'login_status' => 'Success',
        'failure_reason' => null,
        'twofa_method' => $twofaMethod,
        'tls_enabled' => $tlsEnabled,
        'suspicious_flag' => 0
    ]);

    // Redirect to 2FA page if a code was sent, otherwise go to dashboard
    if ($codeSent) {
        header("Location: indexlogincode.php");
        exit;
    } else {
        $_SESSION['messagestate'] = 'info';
        $_SESSION['mess'] = "Welcome back, $fullnames";
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>SMS | Login</title>
    <link rel="icon" href="images/tabpic.png">
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
        /* (same styling as before, omitted here for brevity) */
        body { font-family: 'Consolas', monospace; font-size:14px; background: url('images/loginback.jpg') no-repeat center center fixed; background-size:cover; margin:0; padding:0; display:flex; justify-content:center; align-items:center; height:100vh; }
        .login-wrapper { display:flex; max-width:520px; width:100%; background:linear-gradient(145deg,#ffffff,#f0f0f0); box-shadow:0 8px 20px rgba(0,0,0,.2); border-radius:10px; overflow:hidden; margin:10px; }
        .logo-section { background:linear-gradient(45deg, rgba(8,123,255,0.9), rgba(100,200,255,0.7)); padding:20px; width:40%; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; }
        .form-section { padding:24px; width:60%; display:flex; flex-direction:column; justify-content:center; }
        .form-section h2 { font-size:24px; margin-bottom:20px; color:#004B6E; }
        .form-section input, .form-section button { width:100%; padding:10px; margin-bottom:12px; border-radius:5px; }
        .form-section button { background:#004B6E; color:#fff; border:none; cursor:pointer; }
        @media (max-width:768px){ .login-wrapper{flex-direction:column} .logo-section,.form-section{width:100%} }
    
    </style>
</head>
<body>
   

    <div class="login-wrapper" role="main" aria-label="Login">
        <div class="logo-section">
            <?php
            $searchquery = "SELECT * FROM schooldetails LIMIT 1";
            $qry = $dbh->prepare($searchquery);
            $qry->execute();
            if ($rlt = $qry->fetch(PDO::FETCH_OBJ)) {
                $schoolname = htmlentities($rlt->schoolname, ENT_QUOTES, 'UTF-8');
                echo "<h2 style=\"font-family:'Poppins',sans-serif;font-size:28px;color:#2a2f45;\">".htmlspecialchars($schoolname)."</h2>";
                echo "<img src=\"images/schoollogo.png\" alt=\"{$schoolname}\" style=\"max-width:120px;margin-top:12px;\"/>";
            } else {
                echo "<h2>School Management System</h2>";
            }
            ?>
        </div>
                           
        <div class="form-section">
    <h2>Login</h2>

    <?php if (!empty($_SESSION['mess'])): ?>
        <p style="color:<?php echo ($_SESSION['messagestate']=='deleted' ? 'red' : 'blue'); ?>; font-weight:bold;">
            <?php echo htmlspecialchars($_SESSION['mess']); ?>
        </p>
        <?php 
        unset($_SESSION['mess']); 
        unset($_SESSION['messagestate']); 
    endif; ?>

    <!-- Login form: includes CSRF and device fingerprint hidden inputs -->
    <form method="post" name="login" id="loginForm" autocomplete="off" onsubmit="return beforeSubmit();">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="hidden" name="device_fingerprint" id="device_fingerprint" value="">

        <label for="username">Username</label>
        <input type="text" name="username" id="username" autocomplete="username" placeholder="Enter your username" required>

        <label for="password">Password</label>
        <div style="position:relative;">
            <input type="password" name="password" id="password" autocomplete="current-password" placeholder="Enter your password" required>
            <span id="togglePassword" style="position:absolute; right:10px; top:10px; cursor:pointer;">
                <i class="bi bi-eye" id="passwordIcon"></i>
            </span>
        </div>

        <button type="submit" name="login" id="submitBtn">Login</button>
        <p><a href="forgot-password.php">Forgot Password?</a></p>
    </form>
</div>

<script>
/* Toggle password visibility */
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
const passwordIcon = document.getElementById('passwordIcon');
togglePassword.addEventListener('mousedown', () => { passwordInput.type = 'text'; passwordIcon.className = 'bi bi-eye-slash'; });
togglePassword.addEventListener('mouseup', () => { passwordInput.type = 'password'; passwordIcon.className = 'bi bi-eye'; });
togglePassword.addEventListener('mouseleave', () => { passwordInput.type = 'password'; passwordIcon.className = 'bi bi-eye'; });

/* Build a deterministic lightweight device fingerprint on client and set hidden field.
   Note: This is intentionally simple; for stronger fingerprinting consider a dedicated library.
*/
function buildDeviceFingerprint() {
    try {
        const ua = navigator.userAgent || '';
        const platform = navigator.platform || '';
        const lang = navigator.language || navigator.userLanguage || '';
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        const screenSize = (screen && screen.width) ? (screen.width + 'x' + screen.height) : '';
        const plugins = (navigator.plugins && navigator.plugins.length) ? navigator.plugins.length : 0;
        const components = [ua, platform, lang, tz, screenSize, plugins].join('|');
        // simple SHA-256 using SubtleCrypto
        if (window.crypto && crypto.subtle) {
            const enc = new TextEncoder();
            return crypto.subtle.digest('SHA-256', enc.encode(components)).then(buf => {
                const hex = Array.from(new Uint8Array(buf)).map(b => b.toString(16).padStart(2,'0')).join('');
                document.getElementById('device_fingerprint').value = hex;
                return hex;
            }).catch(()=> {
                // fallback hash
                const fallback = sha256Fallback(components);
                document.getElementById('device_fingerprint').value = fallback;
                return fallback;
            });
        } else {
            const fallback = sha256Fallback(components);
            document.getElementById('device_fingerprint').value = fallback;
            return Promise.resolve(fallback);
        }
    } catch (e) {
        document.getElementById('device_fingerprint').value = '';
        return Promise.resolve('');
    }
}

/* Minimal JS SHA-256 fallback (non-crypto), used only when SubtleCrypto unavailable.
   Not cryptographically ideal but deterministic for fingerprinting.
*/
function sha256Fallback(str) {
    // simple JS SHA-256 implementation (compact)
    function rightRotate(n, x){ return (x>>>n) | (x << (32-n)); }
    var H = [0x6a09e667,0xbb67ae85,0x3c6ef372,0xa54ff53a,0x510e527f,0x9b05688c,0x1f83d9ab,0x5be0cd19];
    var K = [
        0x428a2f98,0x71374491,0xb5c0fbcf,0xe9b5dba5,0x3956c25b,0x59f111f1,0x923f82a4,0xab1c5ed5,
        0xd807aa98,0x12835b01,0x243185be,0x550c7dc3,0x72be5d74,0x80deb1fe,0x9bdc06a7,0xc19bf174,
        0xe49b69c1,0xefbe4786,0x0fc19dc6,0x240ca1cc,0x2de92c6f,0x4a7484aa,0x5cb0a9dc,0x76f988da,
        0x983e5152,0xa831c66d,0xb00327c8,0xbf597fc7,0xc6e00bf3,0xd5a79147,0x06ca6351,0x14292967,
        0x27b70a85,0x2e1b2138,0x4d2c6dfc,0x53380d13,0x650a7354,0x766a0abb,0x81c2c92e,0x92722c85,
        0xa2bfe8a1,0xa81a664b,0xc24b8b70,0xc76c51a3,0xd192e819,0xd6990624,0xf40e3585,0x106aa070,
        0x19a4c116,0x1e376c08,0x2748774c,0x34b0bcb5,0x391c0cb3,0x4ed8aa4a,0x5b9cca4f,0x682e6ff3,
        0x748f82ee,0x78a5636f,0x84c87814,0x8cc70208,0x90befffa,0xa4506ceb,0xbef9a3f7,0xc67178f2
    ];
    function toBytes(s){
        var bytes = [];
        for (var i=0;i<s.length;i++){
            var code = s.charCodeAt(i);
            if (code < 0x80) bytes.push(code);
            else if (code < 0x800){ bytes.push(0xc0 | (code>>6), 0x80 | (code & 0x3f)); }
            else if (code < 0xd800 || code >= 0xe000){ bytes.push(0xe0 | (code>>12), 0x80 | ((code>>6)&0x3f), 0x80 | (code & 0x3f)); }
            else { i++; var code2 = 0x10000 + (((code & 0x3ff)<<10) | (s.charCodeAt(i) & 0x3ff)); bytes.push(0xf0 | (code2>>18), 0x80 | ((code2>>12)&0x3f), 0x80 | ((code2>>6)&0x3f), 0x80 | (code2 & 0x3f)); }
        }
        return bytes;
    }
    var bytes = toBytes(str);
    var bitLen = bytes.length * 8;
    bytes.push(0x80);
    while ((bytes.length % 64) !== 56) bytes.push(0);
    for (var i = 7; i >= 0; i--) bytes.push((bitLen >>> (i*8)) & 0xff);

    var w = new Array(64);
    for (var i=0;i<bytes.length;i+=64){
        for (var t=0;t<16;t++){
            w[t] = (bytes[i + (t*4)]<<24) | (bytes[i + (t*4)+1]<<16) | (bytes[i + (t*4)+2]<<8) | (bytes[i + (t*4)+3]);
        }
        for (var t=16;t<64;t++){
            var s0 = (rightRotate(7,w[t-15]) ^ rightRotate(18,w[t-15]) ^ (w[t-15]>>>3));
            var s1 = (rightRotate(17,w[t-2]) ^ rightRotate(19,w[t-2]) ^ (w[t-2]>>>10));
            w[t] = (w[t-16] + s0 + w[t-7] + s1) >>> 0;
        }
        var a=H[0], b=H[1], c=H[2], d=H[3], e=H[4], f=H[5], g=H[6], h=H[7];
        for (var t=0;t<64;t++){
            var S1 = (rightRotate(6,e) ^ rightRotate(11,e) ^ rightRotate(25,e));
            var ch = ((e & f) ^ ((~e) & g));
            var temp1 = (h + S1 + ch + K[t] + w[t]) >>> 0;
            var S0 = (rightRotate(2,a) ^ rightRotate(13,a) ^ rightRotate(22,a));
            var maj = ((a & b) ^ (a & c) ^ (b & c));
            var temp2 = (S0 + maj) >>> 0;
            h = g; g = f; f = e; e = (d + temp1) >>> 0;
            d = c; c = b; b = a; a = (temp1 + temp2) >>> 0;
        }
        H[0] = (H[0] + a) >>> 0;
        H[1] = (H[1] + b) >>> 0;
        H[2] = (H[2] + c) >>> 0;
        H[3] = (H[3] + d) >>> 0;
        H[4] = (H[4] + e) >>> 0;
        H[5] = (H[5] + f) >>> 0;
        H[6] = (H[6] + g) >>> 0;
        H[7] = (H[7] + h) >>> 0;
    }
    var hex = '';
    for (var i=0;i<H.length;i++) hex += ('00000000' + (H[i]>>>0).toString(16)).slice(-8);
    return hex;
}

/* Called before form submit to ensure device fingerprint is present */
function beforeSubmit() {
    // If fingerprint already computed, allow submit
    if (document.getElementById('device_fingerprint').value) return true;
    // Otherwise compute then submit
    buildDeviceFingerprint().then(() => {
        document.getElementById('loginForm').submit();
    });
    return false;
}

/* compute fingerprint early (best-effort) */
buildDeviceFingerprint();
</script>
</body>
</html>
