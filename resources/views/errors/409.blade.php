<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terjadi Konflik Sesi - OrangBaik.id</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .error-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            max-width: 480px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .error-icon {
            width: 72px;
            height: 72px;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 24px;
        }
        h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 12px;
            color: #0f172a;
        }
        p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin: 0 0 28px;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            flex-direction: column;
        }
        .btn-primary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #10b981;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-primary-action:hover {
            background: #059669;
        }
        .btn-secondary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #f1f5f9;
            color: #475569;
            font-weight: 500;
            font-size: 14px;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-secondary-action:hover {
            background: #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h1>Status Sesi Kedaluwarsa</h1>
        <p>Permintaan Anda tidak dapat diproses karena sesi telah diperbarui atau token kedaluwarsa. Silakan muat ulang halaman atau login kembali.</p>
        <div class="btn-group">
            <a href="{{ route('login') }}" class="btn-primary-action">
                <i class="bi bi-box-arrow-in-right"></i> Masuk Kembali
            </a>
            <a href="{{ route('home') }}" class="btn-secondary-action">
                <i class="bi bi-house"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
