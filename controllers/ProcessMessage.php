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

            $urlPdf = BASE_URL . '/upload/documents/' . $reg['registration_no'] . '.pdf';
            return $this->responFormatter->line('Berikut detail pendaftaran anda')
                ->responAsDocument($urlPdf, "Pendaftaran " . $reg['registration_no'] . ".pdf", 'pdf');
        }

        $cache = $this->cache->get($from);
        if (!$cache) return false;
        $currentStep = $cache['step'];


        switch ($currentStep) {
            case 'daftarppdb':
                // Simpan Nama Lengkap yang diterima dari pengguna
                $this->setStep($from, 'jenis_kelamin', ['nama_lengkap' => $msg]);
                return $this->responFormatter->line('2.) Silahkan ketik Jenis Kelamin Anda : ')
                    ->italic('L = Laki-laki, P = Perempuan')
                    ->responAsText();
                break;
            case 'jenis_kelamin':
                // Validasi jenis kelamin yang diterima dari pengguna
                if (strtolower($msg) === 'l' || strtolower($msg) === 'p') {
                    $this->setStep($from, 'nama_sekolah', ['jenis_kelamin' => strtoupper($msg)]);
                    return $this->responFormatter->line('3.) Silahkan ketik Nama Sekolah Asal Anda')
                        ->responAsText();
                } else {
                    return $this->responFormatter->line('Mohon maaf, silakan ketikkan jenis kelamin dengan benar (L/P)')
                        ->responAsText();
                }
                break;
                // Kasus selanjutnya tetap sama untuk menyimpan data, hanya langkah yang berbeda.
            case 'nama_sekolah':
                $this->setStep($from, 'NISN', ['nama_sekolah' => $msg]);
                return $this->responFormatter->line('4.) Silahkan ketik *NISN* ')->italic('(Nomor Induk Siswa Nasional)')
                    ->line()->code('Isi "-" (strip) jika tidak ada NISN')
                    ->responAsText();
                break;
            case 'NISN':
                $this->setStep($from, 'NIK', ['NISN' => $msg]);
                return $this->responFormatter->line('5.) Silahkan ketik *NIK* _(Nomor Induk Kependudukan)_ Anda')
                    ->line()->code('Isi "-" (strip) jika tidak ada NISN')
                    ->responAsText();
                break;
            case 'NIK':
                $this->setStep($from, 'alamat_jalan', ['NIK' => $msg]);
                return $this->responFormatter->line('6.) Silahkan ketik Alamat  Anda')
                    ->line()->code('Hanya nama Jalan / Dusun')
                    ->responAsText();
                break;
            case 'alamat_jalan':
                $this->setStep($from, 'alamat_kota', ['alamat_jalan' => $msg]);
                return $this->responFormatter->line('7.) Silahkan ketik Alamat Anda')
                    ->line()->code('Nama Kota / Kabupaten')
                    ->responAsText();
                break;
            case 'alamat_kota':
                $this->setStep($from, 'alamat_rtrw', ['alamat_kota' => $msg]);
                return $this->responFormatter->line('8.) Silahkan ketik Alamat Anda')
                    ->line()->code('RT/RW, Contoh: 01/02')
                    ->responAsText();
                break;
            case 'alamat_rtrw':
                $regNo = $this->ppdbRepository->insertPpdb(
                    $cache['data']['nama_lengkap'],
                    $cache['data']['jenis_kelamin'],
                    $cache['data']['nama_sekolah'],
                    $cache['data']['NISN'],
                    $cache['data']['NIK'],
                    $cache['data']['alamat_jalan'],
                    $cache['data']['alamat_kota'],
                    $msg,
                    $from
                );
                $this->cache->delete($from);
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
                break;
            default:
                // Jika tidak ada langkah yang sesuai, kembalikan pesan default
                return $this->responFormatter->line('Maaf, saya tidak mengerti pesan Anda.')
                    ->responAsText();
                break;
        }
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
