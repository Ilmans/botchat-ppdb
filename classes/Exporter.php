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
        $headers = ['No Registrasi', 'Nama Lengkap', 'L/P', 'Asal Sekolah', 'NISN', 'NIK', 'Alamat', 'Telepon'];
        $columnLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
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
            $sheet->setCellValue('C' . $rowIndex, $ppdb['gender']);
            $sheet->setCellValue('D' . $rowIndex, $ppdb['school_name']);
            $sheet->setCellValue('E' . $rowIndex, $ppdb['nisn']);
            $sheet->setCellValue('F' . $rowIndex, $ppdb['nik']);
            $sheet->setCellValue('G' . $rowIndex, $ppdb['street'] . ', ' . $ppdb['city'] . ', ' . $ppdb['rtrw']);
            $sheet->setCellValue('H' . $rowIndex, $ppdb['phone']);
            $rowIndex++;
        }

        // Save the spreadsheet to a .xlsx file
        $writer = new Xlsx($spreadsheet);
        $writer->save(ROOT_PATH. '/upload/ppdb_data.xlsx');
        return ROOT_PATH. '/upload/ppdb_data.xlsx';
    }
}
