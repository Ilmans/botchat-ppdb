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
        // find by username and access true
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            if (!$result['access']) {
                return false;
            }

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

    public function createAccount($username, $email, $password)
    {
        // hash password
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare('INSERT INTO users (username, password, email) VALUES (:username, :password, :email)');
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    }
}
