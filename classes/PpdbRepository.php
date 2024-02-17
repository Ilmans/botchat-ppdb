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
    public function insertPpdb($name, $gender, $schoolName, $nisn, $nik, $street, $city, $rtrw, $phone)
    {
        $regNo = $this->randomRegistrationNumber();
        $sql = "INSERT INTO ppdbs (registration_no,full_name,gender,school_name,nisn,nik,street,city,rtrw,phone) VALUES (:registration_no,:full_name,:gender,:school_name,:nisn,:nik,:street,:city,:rtrw,:phone)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'registration_no' => $regNo,
            'full_name' => $name,
            'gender' => $gender,
            'school_name' => $schoolName,
            'nisn' => $nisn,
            'nik' => $nik,
            'street' => $street,
            'city' => $city,
            'rtrw' => $rtrw,
            'phone' => $phone,

        ]);
        return $regNo;
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
