<?php

namespace classes;

class Auth
{
    protected $conn;
    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function doLogin($username, $password)
    {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            if (password_verify($password, $result['password'])) {
                $_SESSION['username'] = $username;
                return true;
            }
        }
        return false;
    }


    public function logout()
    {
        session_destroy();
    }

    public function isLogin()
    {
        return isset($_SESSION['username']);
    }

    public function createAccount($username, $password)
    {
        // hash password
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        return $stmt->execute();
    }
}
