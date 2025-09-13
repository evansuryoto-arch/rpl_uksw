<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Asesmen RPL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-2">Registrasi Mahasiswa</h1>
            <p class="text-center text-gray-500 mb-6">Buat akun baru Anda</p>
            
            <?= validation_errors('<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>', '</p></div>'); ?>

            <?= form_open('auth/process_register'); ?>
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?= set_value('name') ?>" required>
                </div>
                 <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?= set_value('username') ?>" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>
                <div class="mb-6">
                   <label for="prodi" class="block text-sm font-medium text-gray-700 mb-1">Pilih Program Studi</label>
                   <select id="prodi" name="prodi_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                       <option value="">-- Pilih Prodi --</option>
                       <?php foreach($program_studies as $prodi): ?>
                           <option value="<?= $prodi->id ?>"><?= $prodi->name ?></option>
                       <?php endforeach; ?>
                   </select>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700">Daftar</button>
            <?= form_close(); ?>
            <div class="mt-4 text-center text-sm">
                <p class="text-gray-600">Sudah punya akun? <a href="<?= site_url('auth') ?>" class="font-semibold text-indigo-600 hover:text-indigo-500">Masuk di sini</a></p>
            </div>
        </div>
    </div>
</body>
</html>
