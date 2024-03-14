<?php

namespace classes;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Exporter
{
    private $ppdbRepository;

    public function __construct(PpdbRepository $ppdbRepository)
    {
        $this->ppdbRepository = $ppdbRepository;
    }

    public function exportAllData()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set the headers
        $headers = ['No Registrasi', 'Nama Lengkap', 'Alamat Siswa', 'TTL', 'L/P', 'Golongan Darah', 'Asal Sekolah', 'No Ijazah', 'NISN', 'Agama', 'No HP Siswa', 'Data Ayah', 'Data Ibu', 'Alamat Orang Tua', 'Data Wali', 'Pilihan Jurusan', 'KIP', 'Sumber Informasi'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columnLetters[$index] . '1', $header);
        }

        // Fetch all data
        $ppdbs = $this->ppdbRepository->getAll();

        // Start from the second row, because the first row is the header
        $rowIndex = 2;
        foreach ($ppdbs as $ppdb) {
            $sheet->setCellValue('A' . $rowIndex, $ppdb['registration_no']);
            $sheet->setCellValue('B' . $rowIndex, $ppdb['full_name']);
            $sheet->setCellValue('C' . $rowIndex, $ppdb['student_address']);
            $sheet->setCellValue('D' . $rowIndex, $ppdb['ttl']);
            $sheet->setCellValue('E' . $rowIndex, $ppdb['gender']);
            $sheet->setCellValue('F' . $rowIndex, $ppdb['blood_type']);
            $sheet->setCellValue('G' . $rowIndex, $ppdb['school_origin'] . ' (' . $ppdb['school_origin_type'] . ') alamat: ' . $ppdb['school_origin_address']);
            $sheet->setCellValue('H' . $rowIndex, $ppdb['ijazah_number']);
            $sheet->setCellValue('I' . $rowIndex, $ppdb['nisn']);
            $sheet->setCellValue('J' . $rowIndex, $ppdb['religion']);
            $sheet->setCellValue('K' . $rowIndex, $ppdb['student_phone']);
            $sheet->setCellValue('L' . $rowIndex, $ppdb['father_name'] . ', ' . $ppdb['father_job'] . ', ' . $ppdb['father_phone']);
            $sheet->setCellValue('M' . $rowIndex, $ppdb['mother_name'] . ', ' . $ppdb['mother_job'] . ', ' . $ppdb['mother_phone']);
            $sheet->setCellValue('N' . $rowIndex, $ppdb['parents_address']);
            $sheet->setCellValue('O' . $rowIndex, $ppdb['guardian_name'] . ', ' . $ppdb['guardian_job'] . ', ' . $ppdb['guardian_phone'] . ', ' . $ppdb['guardian_relationship'] . ', ' . $ppdb['guardian_address'] . ' (' . $ppdb['guardian_relationship'] . ')');
            $sheet->setCellValue('P' . $rowIndex, '1. ' . $ppdb['first_choice'] . ' 2. ' . $ppdb['second_choice']);
            $sheet->setCellValue('Q' . $rowIndex, $ppdb['has_kip'] ? 'Ya' : 'Tidak');
            $sheet->setCellValue('R' . $rowIndex, $ppdb['information_source'] . ' (' . $ppdb['friend_name'] . ')');
            $rowIndex++;
        }

        // Save the spreadsheet to a .xlsx file
        $writer = new Xlsx($spreadsheet);
        $writer->save(ROOT_PATH . '/upload/ppdb_data.xlsx');
        return ROOT_PATH . '/upload/ppdb_data.xlsx';
    }
}
