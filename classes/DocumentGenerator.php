<?php



namespace classes;


use PhpOffice\PhpWord\TemplateProcessor;

class DocumentGenerator
{





    public function generateDocument($data)
    {
        // Fetch user data from the database



        // Load the template processor
        // check existed file .doc or .docx
        if (file_exists(ROOT_PATH . '/upload/template.doc')) {
            $templateProcessor = new TemplateProcessor(ROOT_PATH . '/upload/template.doc');
        } else {
            $templateProcessor = new TemplateProcessor(ROOT_PATH . '/upload/template.docx');
        }


        // Replace placeholders with actual values
        // Replace placeholders with actual values
        $templateProcessor->setValue('nama', $data['full_name']);
        $templateProcessor->setValue('nisn', $data['nisn']);
        $templateProcessor->setValue('ttl', $data['ttl']);
        $templateProcessor->setValue('jenis_kelamin', $data['gender'] == 'L' ? 'Laki-laki' : 'Perempuan');
        $templateProcessor->setValue('agama', $data['religion']);
        $templateProcessor->setValue('alamat_siswa', $data['student_address']);
        $templateProcessor->setValue('sekolah_asal', $data['school_origin']);
        $templateProcessor->setValue('tipe_sekolah_asal', $data['school_origin_type']);
        $templateProcessor->setValue('alamat_sekolah_asal', $data['school_origin_address']);
        $templateProcessor->setValue('jurusan_pertama', $data['first_choice']);
        $templateProcessor->setValue('jurusan_kedua', $data['second_choice']);
        $templateProcessor->setValue('kip', $data['has_kip'] ? 'Ya' : 'Tidak');
        $templateProcessor->setValue('nama_ayah', $data['father_name']);
        $templateProcessor->setValue('nama_ibu', $data['mother_name']);
        $templateProcessor->setValue('alamat_orang_tua', $data['parents_address']);
        $templateProcessor->setValue('nohp_orang_tua', $data['father_phone']); // Assuming father's phone is the main contact
        $templateProcessor->setValue('pekerjaan_ayah', $data['father_job']);
        $templateProcessor->setValue('pekerjaan_ibu', $data['mother_job']);
        $templateProcessor->setValue('nama_wali', $data['guardian_name']);
        $templateProcessor->setValue('alamat_wali', $data['guardian_address']);
        $templateProcessor->setValue('nohp_wali', $data['guardian_phone']);
        $templateProcessor->setValue('pekerjaan_wali', $data['guardian_job']);
        if ($data['transfer']) {
            $templateProcessor->setImageValue('image', array(
                'path' => ROOT_PATH . '/upload/transfer/' . $data['registration_no'] . '.jpg',
                'width' => 50,
                'height' => 50,
                'ratio' => false
            ));
        }

        // Save the new document to a temp file
        $temp_file = ROOT_PATH . '/upload/documents/' . $data['registration_no'] . '.docx';
        $templateProcessor->saveAs($temp_file);

        //Settings::setPdfRenderer(Settings::PDF_RENDERER_TCPDF, ROOT_PATH . '/vendor/tecnickcom/tcpdf');

        // Convert to PDF
        // $phpWord = IOFactory::load($temp_file);
        // $xmlWriter = IOFactory::createWriter($phpWord, 'PDF');
        // $xmlWriter->save(ROOT_PATH . '/upload/documents/' . $data['registration_no'] . '.pdf');

        // Delete the temp file
        //  unlink($temp_file);
        return true;
    }

    public function getDocument($data)
    {
        $file = ROOT_PATH . '/upload/documents/' . $data['registration_no'] . '.pdf';

        $this->generateDocument($data);

        return $file;
    }
}
