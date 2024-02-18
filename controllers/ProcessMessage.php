<?php

namespace controllers;

use classes\Cache;
use classes\DocumentGenerator;
use classes\PpdbRepository;
use classes\ResponWebhookFormatter;

class ProcessMessage
{
    private $responFormatter;
    private PpdbRepository $ppdbRepository;
    private DocumentGenerator $documentGenerator;
    private $cache;

    public function __construct()
    {
        $this->responFormatter = new ResponWebhookFormatter();
        $this->ppdbRepository = new PpdbRepository();
        $this->documentGenerator = new DocumentGenerator();
        $this->cache = new Cache();
    }

    private function setStep($from, $step, $newData = [])
    {
        $previousData = $this->cache->get($from) ?? ['step' => '', 'data' => []];
        $this->cache->set($from, [
            'step' => $step,
            'data' => array_merge($previousData['data'] ?? [], $newData)
        ], 3600);
    }

    public function process($msg, $from, $bufferImage)
    {

        if ($bufferImage) {
            return $this->processImageMessage($msg, $from, $bufferImage);
        }
        switch ($msg) {
            case 'ppdb':
                return $this->ppdbMsg($from);
                break;
            case 'daftarppdb':
                return $this->daftarPPDB($from);
                break;
            default:
                return $this->processUnlistedMessage($msg, $from, $bufferImage);
                break;
        }
    }

    private function ppdbMsg($from)
    {
        $this->cache->delete($from);
        return $this->responFormatter->line('Selamat datang di PPDB Online')
            ->line('sebelum melanjutkan ke langkah berikutnya, silahkan persiapkan')->bold('FC,KK,BUKTI TRANSFER DAN KARTU PELAJAR')
            ->separator()->line('Silahkan ketik *daftarppdb* untuk mendaftar')
            ->responAsText();
    }

    private function daftarPPDB($from)
    {
        $this->cache->delete($from);
        $this->setStep($from, 'daftarppdb');
        return $this->responFormatter->line('1.) Silahkan ketik Nama Lengkap anda')
            ->bold('Pastikan sesuai dengan yang tertulis di ijazah SD')->quoted()
            ->responAsText();
    }

    private function processUnlistedMessage($msg, $from, $bufferImage)
    {
        // Ambil langkah saat ini dari cache
        if (strpos($msg, 'cekpendaftaran.') !== false) {
            $regNo = str_replace('cekpendaftaran.', '', $msg);
            $reg = $this->ppdbRepository->checkAndGetByRegistrationNumber($regNo, $bufferImage);
            if (!$reg) {
                return $this->responFormatter->line('Nomor pendaftaran tidak ditemukan')
                    ->responAsText();
            }
            $this->documentGenerator->getDocument($reg);

            $urlPdf = BASE_URL . '/upload/documents/' . $reg['registration_no'] . '.docx';
            return $this->responFormatter->line('Berikut detail pendaftaran anda')
                ->responAsDocument($urlPdf, "Pendaftaran " . $reg['registration_no'] . ".docx", 'docx');
        }

        $cache = $this->cache->get($from);
        if (!$cache) return false;
        $currentStep = $cache['step'];

        $konsentrasi = [
            'a' => 'Akuntansi dan Keungan Lembaga',
            'b' => 'Manajemen Perkantoran dan Layanan Bisnis',
            'c' => 'Desain Produksi Busana',
            'd' => 'Kuliner',
            'e' => 'Teknik Otomotif',
            'f' => 'Desain Komunikasi Visual'
        ];

        switch ($currentStep) {
            case 'daftarppdb':
                $this->setStep($from, 'tempat_tanggal_lahir', ['nama_lengkap' => $msg]);
                return $this->responFormatter->line('2.) Silahkan ketik Tempat, Dan Tanggal Lahir Anda')
                    ->code('Contoh: Jakarta, 17 Agustus 1945')
                    ->responAsText();
                break;
            case 'tempat_tanggal_lahir':
                $this->setStep($from, 'jenis_kelamin', ['tempat_tanggal_lahir' => $msg]);
                return $this->responFormatter->line('3.) Silahkan ketik Jenis Kelamin Anda')
                    ->italic('L = Laki-laki, P = Perempuan')
                    ->responAsText();
                break;
            case 'jenis_kelamin':
                if (strtolower($msg) === 'l' || strtolower($msg) === 'p') {
                    $this->setStep($from, 'golongan_darah', ['jenis_kelamin' => strtoupper($msg)]);
                    return $this->responFormatter->line('4.) Silahkan ketik Golongan Darah Anda (A/B/AB/O/)')
                        ->code('ketik (-) strip jika tidak tahu')
                        ->responAsText();
                } else {
                    return $this->responFormatter->line('Mohon maaf, silakan ketikkan jenis kelamin dengan benar (L/P)')
                        ->responAsText();
                }
                break;
            case 'golongan_darah':
                $validGolonganDarah = ['A', 'B', 'AB', 'O', '-'];
                if (in_array(strtoupper($msg), $validGolonganDarah)) {
                    $this->setStep($from, 'asal_sekolah', ['golongan_darah' => $msg]);
                    return $this->responFormatter->line('4.) Silahkan ketik Asal Sekolah Anda')
                        ->responAsText();
                } else {
                    return $this->responFormatter->line('Mohon maaf, silakan ketikkan golongan darah dengan benar')
                        ->code('A/B/AB/O/-')
                        ->responAsText();
                }
                break;
            case 'asal_sekolah':
                $this->setStep($from, 'tipe_asal_sekolah', ['asal_sekolah' => $msg]);
                return $this->responFormatter->line('6.) Tipe asal sekolah anda, SMP/MTS ?')
                    ->responAsText();
                break;
            case 'tipe_asal_sekolah':
                if (strtolower($msg) === 'smp' || strtolower($msg) === 'mts') {
                    $this->setStep($from, 'alamat_asal_sekolah', ['tipe_asal_sekolah' => $msg]);
                    return $this->responFormatter->line('7.) Silahkan ketik Alamat Asal Sekolah Anda')
                        ->responAsText();
                } else {
                    return $this->responFormatter->line('Mohon maaf, silakan ketikkan tipe asal sekolah dengan benar')
                        ->code('SMP/MTS')
                        ->responAsText();
                }
                $this->setStep($from, 'alamat_asal_sekolah', ['tipe_asal_sekolah' => $msg]);
                return $this->responFormatter->line('7.) Silahkan ketik Alamat Asal Sekolah Anda')
                    ->responAsText();
                break;
            case 'alamat_asal_sekolah':
                $this->setStep($from, 'no_ijazah', ['alamat_asal_sekolah' => $msg]);
                return $this->responFormatter->line('7.) Silahkan ketik No. Ijazah Anda')
                    ->responAsText();
                break;
            case 'no_ijazah':
                $this->setStep($from, 'NISN', ['no_ijazah' => $msg]);
                return $this->responFormatter->line('8.) Silahkan ketik NISN (Nomor Induk Siswa Nasional) Anda')
                    ->code('Isi "-" (strip) jika tidak ada NISN')
                    ->responAsText();
                break;
            case 'NISN':
                $this->setStep($from, 'agama', ['NISN' => $msg]);
                return $this->responFormatter->line('9.) Silahkan ketik Agama Anda ')
                    ->code('Islam/Kristen Katolik/Kristen Protestan/Hindu/Budha/Konghuchu')
                    ->responAsText();
                break;
            case 'agama':
                $validAgama = ['ISLAM', 'KRISTEN KATOLIK', 'KRISTEN PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCHU'];
                if (in_array(strtoupper($msg), $validAgama)) {
                    $this->setStep($from, 'alamat_calon_siswa', ['agama' => strtoupper($msg)]);
                    return $this->responFormatter->line('10.) Silahkan ketik alamat lengkap Anda')
                        ->responAsText();
                } else {
                    return $this->responFormatter->line('Mohon maaf, silakan ketikkan agama dengan benar')
                        ->code('Islam/Kristen Katolik/Kristen Protestan/Hindu/Budha/Konghuchu')
                        ->responAsText();
                }
                break;
            case 'alamat_calon_siswa':
                $this->setStep($from, 'nomor_wa_calon_peserta_didik', ['alamat_calon_siswa' => $msg]);
                return $this->responFormatter->line('11.) Silahkan ketik Nomor WA Calon Peserta didik ')
                    ->responAsText();
                break;
            case 'nomor_wa_calon_peserta_didik':
                $this->setStep($from, 'nama_ayah', ['nomor_wa_calon_peserta_didik' => $msg]);
                return $this->responFormatter->line('12.) Silahkan ketik Nama Ayah Anda')
                    ->responAsText();
                break;
            case 'nama_ayah':
                $this->setStep($from, 'pekerjaan_ayah', ['nama_ayah' => $msg]);
                return $this->responFormatter->line('13.) Silahkan ketik Pekerjaan Ayah Peserta Didik')
                    ->responAsText();
                break;
            case 'pekerjaan_ayah':
                $this->setStep($from, 'no_wa_ayah', ['pekerjaan_ayah' => $msg]);
                return $this->responFormatter->line('14.) Silahkan ketik Nomor WA Ayah Peserta Didik')
                    ->code('Isi "-" (strip) jika tidak ada')
                    ->responAsText();
                break;
            case 'no_wa_ayah':
                $this->setStep($from, 'nama_ibu', ['no_wa_ayah' => $msg]);
                return $this->responFormatter->line('15.) Silahkan ketik Nama Ibu Peserta Didik')

                    ->responAsText();
                break;
            case 'nama_ibu':
                $this->setStep($from, 'pekerjaan_ibu', ['nama_ibu' => $msg]);
                return $this->responFormatter->line('16.) Silahkan ketik Pekerjaan Ibu Peserta Didik')
                    ->code('Isi "-" (strip) jika tidak ada')
                    ->responAsText();
                break;
            case 'pekerjaan_ibu':
                $this->setStep($from, 'no_wa_ibu', ['pekerjaan_ibu' => $msg]);
                return $this->responFormatter->line('17.) Silahkan ketik Nomor WA Ibu Anda')
                    ->code('Isi "-" (strip) jika tidak ada')
                    ->responAsText();
                break;
            case 'no_wa_ibu':
                $this->setStep($from, 'alamat_orangtua', ['no_wa_ibu' => $msg]);
                return $this->responFormatter->line('18.) Silahkan ketik Alamat Orangtua Anda')
                    ->responAsText();
                break;
            case 'alamat_orangtua':
                $this->setStep($from, 'nama_wali', ['alamat_orangtua' => $msg]);
                return $this->responFormatter->line('19.) Silahkan ketik Nama Wali Calon Peserta Didik ')
                    ->code('Isi "-" (strip) jika tidak ada')
                    ->responAsText();
                break;
            case 'nama_wali':
                $this->setStep($from, 'pekerjaan_wali', ['nama_wali' => $msg]);
                return $this->responFormatter->line('20.) Silahkan ketik Pekerjaan Wali Peserta Didik')
                    ->code('Isi "-" (strip) jika tidak ada')
                    ->responAsText();
                break;
            case 'pekerjaan_wali':
                $this->setStep($from, 'no_wa_wali', ['pekerjaan_wali' => $msg]);
                return $this->responFormatter->line('21.) Silahkan ketik Nomor WA Wali Peserta Didik')
                    ->code('Isi "-" (strip) jika tidak ada')
                    ->responAsText();
                break;
            case 'no_wa_wali':
                $this->setStep($from, 'hubungan_wali', ['no_wa_wali' => $msg]);
                return $this->responFormatter->line('22.) Silahkan ketik Hubungan wali dengan Peserta Didik')
                    ->code('Isi "-" (strip) jika tidak ada')
                    ->responAsText();
                break;
            case 'hubungan_wali':
                $this->setStep($from, 'alamat_wali', ['hubungan_wali' => $msg]);
                return $this->responFormatter->line('23.) Silahkan ketik Alamat Wali peserta didik')
                    ->code('Isi "-" (strip) jika tidak ada')
                    ->responAsText();
                break;
            case 'alamat_wali':
                $this->setStep($from, 'konsentrasi_pilihan_pertama', ['alamat_wali' => $msg]);
                return $this->responFormatter->line('24.) Silahkan pilih Minat ke Konsentrasi Keahlian (pilihan pertama) Anda:')
                    ->line('a. Akuntansi dan Keungan Lembaga')
                    ->line('b. Manajemen Perkantoran dan Layanan Bisnis')
                    ->line('c. Desain Produksi Busana')
                    ->line('d. Kuliner')
                    ->line('e. Teknik Otomotif')
                    ->line('f. Desain Komunikasi Visual')
                    ->code('pilih a/b/c/d/e/f')
                    ->responAsText();
                break;
            case 'konsentrasi_pilihan_pertama':
                // Validasi pilihan pertama, lalu lanjutkan ke pilihan kedua
                $validChoices = ['a', 'b', 'c', 'd', 'e', 'f'];
                if (in_array(strtolower($msg), $validChoices)) {
                    $this->setStep($from, 'konsentrasi_pilihan_kedua', ['konsentrasi_pilihan_pertama' => $konsentrasi[strtolower($msg)]]);
                    return $this->responFormatter->line('25.) Silahkan pilih Minat ke Konsentrasi Keahlian (pilihan kedua) Anda:')
                        ->line('a. Akuntansi dan Keungan Lembaga')
                        ->line('b. Manajemen Perkantoran dan Layanan Bisnis')
                        ->line('c. Desain Produksi Busana')
                        ->line('d. Kuliner')
                        ->line('e. Teknik Otomotif')
                        ->line('f. Desain Komunikasi Visual')
                        ->code('pilih a/b/c/d/e/f')
                        ->responAsText();
                } else {
                    return $this->responFormatter->line('Pilihan tidak valid. Silakan pilih sesuai dengan opsi yang tersedia.')
                        ->code('pilih a/b/c/d/e/f')
                        ->responAsText();
                }
                break;
            case 'konsentrasi_pilihan_kedua':
                // Validasi pilihan kedua, lalu lanjutkan ke informasi pendaftaran
                $validChoices = ['a', 'b', 'c', 'd', 'e', 'f'];
                if (in_array(strtolower($msg), $validChoices)) {
                    $this->setStep($from, 'info_pendaftaran', ['konsentrasi_pilihan_kedua' => $konsentrasi[strtolower($msg)]]);
                    return $this->responFormatter->line('26.) Dari mana Anda mengetahui informasi pendaftaran ini?')
                        ->line('1. Sosial media (wa/ig/tiktok/youtube)')
                        ->line('2. Presentasi di sekolah anda')
                        ->line('3. Teman')
                        ->line('4. Saudara')
                        ->code('pilih 1/2/3/4')
                        ->responAsText();
                } else {
                    return $this->responFormatter->line('Pilihan tidak valid. Silakan pilih sesuai dengan opsi yang tersedia.')
                        ->responAsText();
                }
                break;
            case 'info_pendaftaran':
                // Validasi info pendaftaran, dan lanjutkan ke KIP
                $validChoices = ['1', '2', '3', '4'];
                if (in_array($msg, $validChoices)) {
                    $infopendaftaran = [
                        '1' => 'Sosial media (wa/ig/tiktok/youtube)',
                        '2' => 'Presentasi di sekolah anda',
                        '3' => 'Teman',
                        '4' => 'Saudara'
                    ];
                    if ($msg == '3') {
                        $this->setStep($from, 'informasi_teman', ['info_pendaftaran' => $infopendaftaran[$msg]]);
                        return $this->responFormatter->line('27.) Jika Anda mengetahui dari teman, silakan sebutkan nama dan kelasnya:')
                            ->responAsText();
                    } else {
                        $this->setStep($from, 'kip', ['info_pendaftaran' => $infopendaftaran[$msg]]);
                        return $this->responFormatter->line('28.) Memiliki Kartu Indonesia Pintar (KIP)? (Ya/Tidak)')
                            ->responAsText();
                    }
                } else {
                    return $this->responFormatter->line('Pilihan tidak valid. Silakan pilih sesuai dengan opsi yang tersedia.')
                        ->responAsText();
                }
                break;
            case 'informasi_teman':
                // Setelah mendapatkan info teman, lanjutkan ke KIP
                $this->setStep($from, 'kip', ['informasi_teman' => $msg]);
                return $this->responFormatter->line('28.) Memiliki Kartu Indonesia Pintar (KIP)? (Ya/Tidak)')
                    ->responAsText();
                break;
            case 'kip':
                // Validasi KIP, dan akhir dari langkah-langkah pendaftaran
                $validChoices = ['ya', 'tidak'];
                if (in_array(strtolower($msg), $validChoices)) {
                    // Proses pendaftaran selesai di sini
                    $data = $this->cache->get($from)['data'];
                    $regNo = $this->ppdbRepository->insertPpdb(
                        $data['nama_lengkap'],
                        $data['tempat_tanggal_lahir'],
                        $data['jenis_kelamin'],
                        $data['golongan_darah'],
                        $data['asal_sekolah'],
                        $data['tipe_asal_sekolah'],
                        $data['alamat_asal_sekolah'],
                        $data['no_ijazah'],
                        $data['NISN'],
                        $data['agama'] ?? '-',
                        $data['alamat_calon_siswa'],
                        $data['nomor_wa_calon_peserta_didik'],
                        $data['nama_ayah'],
                        $data['pekerjaan_ayah'],
                        $data['no_wa_ayah'],
                        $data['nama_ibu'],
                        $data['pekerjaan_ibu'],
                        $data['no_wa_ibu'],
                        $data['alamat_orangtua'],
                        $data['nama_wali'],
                        $data['pekerjaan_wali'],
                        $data['no_wa_wali'],
                        $data['hubungan_wali'],
                        $data['alamat_wali'],
                        $data['konsentrasi_pilihan_pertama'],
                        $data['konsentrasi_pilihan_kedua'],
                        $data['info_pendaftaran'],
                        $data['informasi_teman'],
                        $msg == 'ya' ? 1 : 0

                    );
                    $this->cache->delete($from);
                    // Lakukan penyimpanan data dan respons yang sesuai
                    return $this->responFormatter->line('Terima kasih, Data yang anda masukkan sudah kami terima.')
                        ->separator()->line('Nomor Pendaftaran Anda : *' . $regNo . '*')
                        ->italic('Mohon di simpan nomor pendaftaran tersebut untuk keperluan selanjutnya.')
                        ->separator()
                        ->line('Selanjutnya, anda bisa mengirim bukti pendaftaran (gambar landscape) dengan cara ketik _buktitransfer.nomorpendaftaran_')
                        ->code('Contoh : buktitransfer.PPDB1234')
                        ->separator()
                        ->line('Untuk mengecek detail pendaftaran, silahkan ketik *cekpendaftaran.nomorpendaftaran*')
                        ->line()
                        ->line('Terima kasih')
                        ->responAsText();
                } else {
                    return $this->responFormatter->line('Pilihan tidak valid. Silakan pilih "Ya" atau "Tidak".')
                        ->responAsText();
                }
                break;
            default:
                return $this->responFormatter->line('Maaf, saya tidak mengerti pesan Anda.')
                    ->responAsText();
                break;
        }

        // Ambil langkah saat ini dari cache
        // if (strpos($msg, 'cekpendaftaran.') !== false) {
        //     $regNo = str_replace('cekpendaftaran.', '', $msg);
        //     $reg = $this->ppdbRepository->checkAndGetByRegistrationNumber($regNo, $bufferImage);
        //     if (!$reg) {
        //         return $this->responFormatter->line('Nomor pendaftaran tidak ditemukan')
        //             ->responAsText();
        //     }
        //     $this->documentGenerator->getDocument($reg);

        //     $urlPdf = BASE_URL . '/upload/documents/' . $reg['registration_no'] . '.pdf';
        //     return $this->responFormatter->line('Berikut detail pendaftaran anda')
        //         ->responAsDocument($urlPdf, "Pendaftaran " . $reg['registration_no'] . ".pdf", 'pdf');
        // }

        // $cache = $this->cache->get($from);
        // if (!$cache) return false;
        // $currentStep = $cache['step'];


        // switch ($currentStep) {
        //     case 'daftarppdb':
        //         // Simpan Nama Lengkap yang diterima dari pengguna
        //         $this->setStep($from, 'jenis_kelamin', ['nama_lengkap' => $msg]);
        //         return $this->responFormatter->line('2.) Silahkan ketik Jenis Kelamin Anda : ')
        //             ->italic('L = Laki-laki, P = Perempuan')
        //             ->responAsText();
        //         break;
        //     case 'jenis_kelamin':
        //         // Validasi jenis kelamin yang diterima dari pengguna
        //         if (strtolower($msg) === 'l' || strtolower($msg) === 'p') {
        //             $this->setStep($from, 'nama_sekolah', ['jenis_kelamin' => strtoupper($msg)]);
        //             return $this->responFormatter->line('3.) Silahkan ketik Nama Sekolah Asal Anda')
        //                 ->responAsText();
        //         } else {
        //             return $this->responFormatter->line('Mohon maaf, silakan ketikkan jenis kelamin dengan benar (L/P)')
        //                 ->responAsText();
        //         }
        //         break;
        //         // Kasus selanjutnya tetap sama untuk menyimpan data, hanya langkah yang berbeda.
        //     case 'nama_sekolah':
        //         $this->setStep($from, 'NISN', ['nama_sekolah' => $msg]);
        //         return $this->responFormatter->line('4.) Silahkan ketik *NISN* ')->italic('(Nomor Induk Siswa Nasional)')
        //             ->line()->code('Isi "-" (strip) jika tidak ada NISN')
        //             ->responAsText();
        //         break;
        //     case 'NISN':
        //         $this->setStep($from, 'NIK', ['NISN' => $msg]);
        //         return $this->responFormatter->line('5.) Silahkan ketik *NIK* _(Nomor Induk Kependudukan)_ Anda')
        //             ->line()->code('Isi "-" (strip) jika tidak ada NISN')
        //             ->responAsText();
        //         break;
        //     case 'NIK':
        //         $this->setStep($from, 'alamat_jalan', ['NIK' => $msg]);
        //         return $this->responFormatter->line('6.) Silahkan ketik Alamat  Anda')
        //             ->line()->code('Hanya nama Jalan / Dusun')
        //             ->responAsText();
        //         break;
        //     case 'alamat_jalan':
        //         $this->setStep($from, 'alamat_kota', ['alamat_jalan' => $msg]);
        //         return $this->responFormatter->line('7.) Silahkan ketik Alamat Anda')
        //             ->line()->code('Nama Kota / Kabupaten')
        //             ->responAsText();
        //         break;
        //     case 'alamat_kota':
        //         $this->setStep($from, 'alamat_rtrw', ['alamat_kota' => $msg]);
        //         return $this->responFormatter->line('8.) Silahkan ketik Alamat Anda')
        //             ->line()->code('RT/RW, Contoh: 01/02')
        //             ->responAsText();
        //         break;
        //     case 'alamat_rtrw':
        //         $regNo = $this->ppdbRepository->insertPpdb(
        //             $cache['data']['nama_lengkap'],
        //             $cache['data']['jenis_kelamin'],
        //             $cache['data']['nama_sekolah'],
        //             $cache['data']['NISN'],
        //             $cache['data']['NIK'],
        //             $cache['data']['alamat_jalan'],
        //             $cache['data']['alamat_kota'],
        //             $msg,
        //             $from
        //         );
        //         $this->cache->delete($from);
        //         return $this->responFormatter->line('Terima kasih, Data yang anda masukkan sudah kami terima.')
        //             ->separator()->line('Nomor Pendaftaran Anda : *' . $regNo . '*')
        //             ->italic('Mohon di simpan nomor pendaftaran tersebut untuk keperluan selanjutnya.')
        //             ->separator()
        //             ->line('Selanjutnya, anda bisa mengirim bukti pendaftaran (gambar landscape) dengan cara ketik _buktitransfer.nomorpendaftaran_')
        //             ->code('Contoh : buktitransfer.PPDB1234')
        //             ->separator()
        //             ->line('Untuk mengecek detail pendaftaran, silahkan ketik *cekpendaftaran.nomorpendaftaran*')
        //             ->line()
        //             ->line('Terima kasih')
        //             ->responAsText();
        //         break;
        //     default:
        //         // Jika tidak ada langkah yang sesuai, kembalikan pesan default
        //         return $this->responFormatter->line('Maaf, saya tidak mengerti pesan Anda.')
        //             ->responAsText();
        //         break;
        // }
    }

    private function processImageMessage($msg, $from, $bufferImage)
    {
        if (strpos($msg, 'buktitransfer.') !== false) {
            $regNo = str_replace('buktitransfer.', '', $msg);
            $reg = $this->ppdbRepository->checkAndGetByRegistrationNumber($regNo, $bufferImage);
            if (!$reg) {
                return $this->responFormatter->line('Nomor pendaftaran tidak ditemukan')
                    ->responAsText();
            }
            // not allow if column transfer already filled
            if ($reg['transfer'] !== null) {
                return $this->responFormatter->line('Nomor pendaftaran tersebut sudah mengirimkan bukti transfer, tidak bisa mengirimkan lagi')
                    ->responAsText();
            }
            $this->ppdbRepository->uploadTransfer($regNo, $bufferImage);
            return $this->responFormatter->line('Bukti transfer berhasil diupload')
                ->responAsText();
        }
    }
}
