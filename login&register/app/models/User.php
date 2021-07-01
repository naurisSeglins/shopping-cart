<?php
class User
{
    //private property of the database
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function register($data)
    {
        //1. what will happen and at what table (INSERT INTO users)
        //2. values (username, email, password)
        //3. adding values of the username email and password (:username, :email, :password)
        $this->db->query("INSERT INTO users (username, email, password) VALUES(:username, :email, :password)");

        //Bind values
        $this->db->bind(":username", $data["username"]);
        $this->db->bind(":email", $data["email"]);
        $this->db->bind(":password", $data["password"]);

        //Execute function
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function login($username, $password)
    {
        $this->db->query("SELECT * FROM users WHERE username = :username");

        //Bind value
        $this->db->bind(":username", $username);

        $row = $this->db->single();

        $hashedPassword = $row->password;

        if (password_verify($password, $hashedPassword)) {
            return $row;
        } else {
            return false;
        }
    }


    //Find user by email. Email is passed in by the controller 
    public function findUserByEmail($email)
    {
        //Prepared statement
        $this->db->query("SELECT * FROM users WHERE email = :email");

        //email param will be binded with the email variable
        $this->db->bind(":email", $email);

        //check if email is already registered
        if ($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}