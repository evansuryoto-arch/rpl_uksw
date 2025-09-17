<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard RPL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-menunggu-penilaian { background-color: #fef3c7; color: #92400e; }
        .status-diakui { background-color: #dcfce7; color: #166534; }
        .status-ditolak { background-color: #fee2e2; color: #991b1b; }
        .status-perlu-verifikasi { background-color: #e0e7ff; color: #3730a3; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
<header class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
    Dashboard 
    <?php 
        if ($user['role'] === 'mahasiswa') {
            echo "Calon Mahasiswa";
        } elseif ($user['role'] === 'asesor') {
            echo "Penilai";
        } else {
            echo ucfirst($user['role']);
        }
    ?>
</h1>
            <p class="text-gray-600">Selamat datang, <?= $user['name'] ?>! (<?= $prodi->name ?? 'Prodi tidak ditemukan' ?>)</p>
        </div>
        <a href="<?= site_url('auth/logout') ?>" class="bg-red-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-600 transition-colors">Keluar</a>
    </div>
</header>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
