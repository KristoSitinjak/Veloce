<?php
// Clear all sessions - untuk testing
session_name('VELOCE_USER_SESSION');
session_start();
session_destroy();

session_name('VELOCE_ADMIN_SESSION');
session_start();
session_destroy();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Clear All Sessions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1f3b83 0%, #5b8af0 100%);
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .success {
            color: #28a745;
            font-size: 60px;
            margin-bottom: 20px;
        }
        h1 {
            color: #1f3b83;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
        }
        .links {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        a {
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s;
        }
        a:hover {
            transform: translateY(-2px);
        }
        .btn-user {
            background: #5b8af0;
            color: white;
        }
        .btn-admin {
            background: #dc3545;
            color: white;
        }
        .btn-home {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='success'>✓</div>
        <h1>Semua Session Berhasil Dihapus</h1>
        <p>Session admin dan user telah dibersihkan.<br>Silakan pilih untuk login kembali:</p>
        <div class='links'>
            <a href='login.php' class='btn-user'>Login User</a>
            <a href='admin/login.php' class='btn-admin'>Login Admin</a>
            <a href='index.php' class='btn-home'>Ke Beranda</a>
        </div>
    </div>
</body>
</html>";
?>
