<?php

namespace classes;

use classes\Database;

class PpdbRepository extends Database
{
    protected $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    private function checkRegistrationNumber($regNo)
    {
        $sql = "SELECT * FROM ppdbs WHERE registration_no = :registration_no";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['registration_no' => $regNo]);
        return $stmt->rowCount() > 0;
    }
    private function randomRegistrationNumber()
    {
        $regNo = "";
        do {
            $regNo = "PPDB" . rand(1000, 9999);
        } while ($this->checkRegistrationNumber($regNo));
        return $regNo;
    }
    public function insertPpdb($full_name, $ttl, $gender, $blood_type, $school_origin, $school_origin_type, $school_origin_address, $ijazah_number, $nisn, $religion, $student_address, $student_phone, $father_name, $father_job, $father_phone, $mother_name, $mother_job, $mother_phone, $parents_address, $guardian_name, $guardian_job, $guardian_phone, $guardian_relationship, $guardian_address, $first_choice, $second_choice, $information_source, $friend_name, $has_kip)
    {
        $sql = "INSERT INTO ppdbs (registration_no, full_name, ttl, gender, blood_type, school_origin, school_origin_type, school_origin_address, ijazah_number, nisn, religion, student_address, student_phone, father_name, father_job, father_phone, mother_name, mother_job, mother_phone, parents_address, guardian_name, guardian_job, guardian_phone, guardian_relationship, guardian_address, first_choice, second_choice, information_source, friend_name, has_kip) VALUES (:registration_no, :full_name, :ttl, :gender, :blood_type, :school_origin, :school_origin_type, :school_origin_address, :ijazah_number, :nisn, :religion, :student_address, :student_phone, :father_name, :father_job, :father_phone, :mother_name, :mother_job, :mother_phone, :parents_address, :guardian_name, :guardian_job, :guardian_phone, :guardian_relationship, :guardian_address, :first_choice, :second_choice, :information_source, :friend_name, :has_kip)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'registration_no' => $no = $this->randomRegistrationNumber(),
            'full_name' => $full_name,
            'ttl' => $ttl,
            'gender' => $gender,
            'blood_type' => $blood_type,
            'school_origin' => $school_origin,
            'school_origin_type' => $school_origin_type,
            'school_origin_address' => $school_origin_address,
            'ijazah_number' => $ijazah_number,
            'nisn' => $nisn,
            'religion' => $religion,
            'student_address' => $student_address,
            'student_phone' => $student_phone,
            'father_name' => $father_name,
            'father_job' => $father_job,
            'father_phone' => $father_phone,
            'mother_name' => $mother_name,
            'mother_job' => $mother_job,
            'mother_phone' => $mother_phone,
            'parents_address' => $parents_address,
            'guardian_name' => $guardian_name,
            'guardian_job' => $guardian_job,
            'guardian_phone' => $guardian_phone,
            'guardian_relationship' => $guardian_relationship,
            'guardian_address' => $guardian_address,
            'first_choice' => $first_choice,
            'second_choice' => $second_choice,
            'information_source' => $information_source,
            'friend_name' => $friend_name,
            'has_kip' => $has_kip
        ]);
        return $no;
    }
    public function getAllPaginated($page, $perPage)
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM ppdbs LIMIT :offset, :perPage";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindParam(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get the total number of records
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM ppdbs");
        $stmt->execute();
        $totalRecords = $stmt->fetchColumn();

        // Calculate total pages
        $totalPages = ceil($totalRecords / $perPage);

        return [
            'data' => $results,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function getAll()
    {
        $sql = "SELECT * FROM ppdbs";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function  checkAndGetByRegistrationNumber($regNo)
    {
        $sql = "SELECT * FROM ppdbs WHERE registration_no = :registration_no";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['registration_no' => $regNo]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function uploadTransfer($regNo, $bufferImage)
    {
        // save image to disk
        $image = base64_decode($bufferImage);
        $imageName = ROOT_PATH . '/upload/transfer/' . $regNo . '.jpg';
        file_put_contents($imageName, $image);

        // update database
        $sql = "UPDATE ppdbs SET transfer = :transfer WHERE registration_no = :registration_no";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'transfer' => $imageName,
            'registration_no' => $regNo
        ]);

        return $imageName;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM ppdbs WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
}
