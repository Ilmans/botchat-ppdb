<?php
require 'config/common.php';

use classes\Auth;
use classes\PpdbRepository;

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info mx-4">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}
$ppdbRepository = new PpdbRepository();
$title = "Data PPDB";

$auth = new Auth();
if (!$auth->isLogin()) {
    header('Location: app/login.php');
    exit;
}
$ppdbs = $ppdbRepository->getAllPaginated($_GET['page'] ?? 1, 15);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ppdb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- jquery -->

    <style>
        body {
            padding-top: 70px;
            /* Atur agar konten tidak tertutup oleh navbar */
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .footer-menu {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #343a40;
            padding: 10px 0;
            text-align: center;
            z-index: 9999;
        }

        .footer-menu a {
            color: #ffffff;
            padding: 0 10px;
            text-decoration: none;
        }

        .footer-menu a:hover {
            color: #ffc107;
            /* Ubah warna saat hover */
        }

        .table th,
        .table td {
            font-size: 14px;
        }

        .table-responsive {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            display: inline-block;
            width: 100%;
        }

        .table-responsive thead th {
            position: sticky;
            top: 0;
            background: #343a40;
            color: white;
        }
    </style>
</head>


<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">PPDB</a>
        </div>
    </nav>

    <!-- Content -->
    <div class="px-4 ">
        <!-- Data PPDB Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">No Registrasi</th>
                        <th scope="col">Nama Lengkap</th>
                        <th scope="col">L/P</th>
                        <th scope="col">Asal Sekolah</th>
                        <th scope="col">NISN</th>
                        <th scope="col">NIK</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">Telepon</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ppdbs['data'] as $ppdb) : ?>
                        <tr>
                            <td><?php echo $ppdb['registration_no']; ?></td>
                            <td><?php echo $ppdb['full_name']; ?></td>
                            <td><?php echo $ppdb['gender']; ?></td>
                            <td><?php echo $ppdb['school_name']; ?></td>
                            <td><?php echo $ppdb['nisn']; ?></td>
                            <td><?php echo $ppdb['nik']; ?></td>
                            <td><?php echo $ppdb['street'] . ', ' . $ppdb['city'] . ', ' . $ppdb['rtrw']; ?></td>
                            <td><?php echo $ppdb['phone']; ?></td>
                            <td>
                                <form action="app/delete.php" method="post">
                                    <input type="hidden" name="id" value="<?php echo $ppdb['id']; ?>" />
                                    <button type="submit" class=" " onclick="return confirm('Apakah anda yakin ingin menghapus data ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $ppdbs['total_pages']; $i++) : ?>
                <li class="page-item <?php echo ($i == $ppdbs['current_page']) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
        </ul>
    </nav>

    <!-- Footer Menu -->
    <div class="footer-menu">
        <button class="btn btn-link text-white outline-none" data-bs-toggle="modal" data-bs-target="#uploadTemplateModal"><i class="fas fa-upload"></i> Upload Template</button>
        <a href="app/export.php"><i class="fas fa-download"></i> Export Data</a>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        function lihatBuktiTransfer(id) {
            // Logika untuk melihat bukti transfer
            console.log("Melihat bukti transfer untuk ID: " + id);
        }

        function hapusData(id) {
            // Logika untuk menghapus data
            console.log("Menghapus data untuk ID: " + id);
        }
    </script>
</body>

<!-- upload template modal -->
<div class="modal fade" id="uploadTemplateModal" tabindex="-1" aria-labelledby="uploadTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadTemplateModalLabel">Upload Template</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- download previous template -->

            <div class="modal-body">
                <form action="app/upload.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih file</label>
                        <input type="file" class="form-control" id="file" name="file" required accept=".docx,.doc" />
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

</html>