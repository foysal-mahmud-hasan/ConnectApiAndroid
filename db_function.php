<?php

class DB_FUNCTIONS{

    private $conn;

    //constructor
    function  __construct()
    {

        require_once 'db_connect.php';
        $db = new DB_CONNECT();
        $this->conn = $db->connect();

    }

    //destructor
    function __destruct(){



    }

    //store user detail
    //return user detail
    public function storeUser($name, $email, $password){

        $uuid = uniqid('', true);
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"];
        $salt = $hash["salt"];

        $stmt = $this->conn->prepare("INSERT INTO users(unique_id, name,
                  email, encrypted_password, salt, created_at) VALUES (?,?,?,?,?, NOW())");
        $stmt->bind_param("sssss", $uuid, $name, $email, $encrypted_password, $salt);
        $result = $stmt->execute();
        $stmt->close();

        if($result){

            $stmt = $this->conn->prepare("SELECT * FROM users where email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return $user;

        }else{
            return false;
        }

    }


    //return user by email and password

    public function getUserByEmailAndPassword($email, $password){

        $stmt = $this->conn->prepare("SELECT * FROM users where email=?");
        $stmt->bind_param("s", $email);

        if($stmt->execute()){

            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            //verifying user password

            $salt = $user['salt'];
            $encrypted_password = $user['encrypted_password'];
            $hash = $this->checkHashSSHA($salt, $password);

            //check for password equality
            if($encrypted_password == $hash)
                return $user;
        }else{
            return null;
        }

    }

    //check user is existed or not
    public function isUserExisted($email){
        $stmt = $this->conn->prepare("SELECT email  from users where email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows>0){
            $stmt->close();
            return true;
        }else{
            $stmt->close();
            return false;
        }
    }

    //encrypting password
    public function hashSSHA($password){
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password.$salt, true).$salt);
        return array("salt"=>$salt, "encrypted"=>$encrypted);
    }

    public function checkHashSSHA($salt, $password){
        return base64_encode(sha1($password . $salt,true).$salt);
    }

}

