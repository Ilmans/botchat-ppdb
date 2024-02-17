<?php



namespace classes;


use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class DocumentGenerator
{





    public function generateDocument($data)
    {
        // Fetch user data from the database



        // Load the template processor
        $templateProcessor = new TemplateProcessor(ROOT_PATH . '/upload/template.docx');

        // Replace placeholders with actual values
        $templateProcessor->setValue('nama', $data['full_name']);
        $templateProcessor->setValue('asal_sekolah', $data['school_name']);
        $templateProcessor->setValue('jalan', $data['street']);
        $templateProcessor->setValue('rtrw', $data['rtrw']);
        $templateProcessor->setValue('kota', $data['city']);
        $templateProcessor->setValue('nohp', $data['phone']);
        $templateProcessor->setValue('nisn', $data['nisn']);
        $templateProcessor->setValue('nik', $data['nik']);
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

        Settings::setPdfRenderer(Settings::PDF_RENDERER_DOMPDF, ROOT_PATH . '/vendor/dompdf/dompdf');

        // Convert to PDF
        $phpWord = IOFactory::load($temp_file);
        $xmlWriter = IOFactory::createWriter($phpWord, 'PDF');
        $xmlWriter->save(ROOT_PATH . '/upload/documents/' . $data['registration_no'] . '.pdf');

        // Delete the temp file
        unlink($temp_file);
        return true;
    }

    public function getDocument($data)
    {
        $file = ROOT_PATH . '/upload/documents/' . $data['registration_no'] . '.pdf';

        $this->generateDocument($data);

        return $file;
    }
}
