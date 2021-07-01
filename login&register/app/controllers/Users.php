<?php
class Users extends Controller
{
    //whatever will be inside the constructor will be loaded first whenever a page inside the Users class is called 
    public function __construct()
    {
        $this->userModel = $this->model("User");
    }

    // <-- REGISTER -->
    //this is where all the logical stuff of the register form will happen
    //this method name needs to be the same as the file that we will create
    public function register()
    {
        // <-- DATA -->
        //will go look inside the models find data from the database and it will pass it inside the view 
        //this is an associative array 
        $data = [
            "username" => "",
            "email" => "",
            "password" => "",
            "confirmPassword" => "",
            "usernameError" => "",
            "emailError" => "",
            "passwordError" => "",
            "confirmPasswordError" => ""
        ];

        //determining whether the reguest was a post or a get request
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //sanitize post data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                "username" => trim($_POST["username"]),
                "email" => trim($_POST["email"]),
                "password" => trim($_POST["password"]),
                "confirmPassword" => trim($_POST["confirmPassword"]),
                "usernameError" => "",
                "emailError" => "",
                "passwordError" => "",
                "confirmPasswordError" => ""
            ];

            $nameValidation = "/^[a-zA-Z0-9]*$/";
            $passwordValidation = "/^(.{0,7}|[^a-z]*|[^\d]*)$/i";

            // <-- USERNAME -->
            //Validate that there is a username
            if (empty($data["username"])) {
                $data["usernameError"] = "Please enter username";
                //Validate username on letters and numbers
            } elseif (!preg_match($nameValidation, $data["username"])) {
                $data["usernameError"] = "Name can only contain letters and numbers";
            }

            // <-- EMAIL -->
            //Validate that there is a email
            if (empty($data["email"])) {
                $data["emailError"] = "Please enter email address";
                //Validate the email address
                //this function filters a single variable with a specified filter
            } elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
                $data["emailError"] = "Please enter the correct format";
            } else {
                //check if email exists
                if ($this->userModel->findUserByEmail($data["email"])) {
                    $data["emailError"] = "Email is already taken";
                }
            }

            // <-- PASSWORD -->
            //Validate password on length and numeric values
            if (empty($data["password"])) {
                $data["passwordError"] = "Please enter password";
            } elseif (strlen($data["password"]) < 6) {
                $data["passwordError"] = "Password must be atleast 8 characters";
            } elseif (preg_match($passwordValidation, $data["password"])) {
                $data["passwordError"] = "Password must have atleast one numeric value";
            }

            //Validate confirm password
            if (empty($data["confirmPassword"])) {
                $data["confirPasswordError"] = "Please enter password";
            } else {
                if ($data["password"] != $data["confirmPassword"]) {
                    $data["confirPasswordError"] = "Passwords do not much";
                }
            }

            // Make sure that errors are empty
            if (empty($data["usernameError"]) && empty($data["emailError"]) && empty($data["passwordError"]) && empty($data["confirmPasswordError"])) {
                //Hash password
                $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);

                //Register user from model function
                if ($this->userModel->register($data)) {
                    //Redirect to the login page
                    header("location: " . URLROOT . "/users/login");
                } else {
                    die("Somethin went wrong");
                }
            }
        }
        //define the view by saying that this pointer of view search for a file inside the folder user/login and also pass in the data array $data
        $this->view("users/register", $data);
    }



    // <-- LOGIN -->
    //this is where all the logical stuff of the login form will happen
    //this method name needs to be the same as the file that we will create
    public function login()
    {
        //will go look inside the models find data from the database and it will pass it inside the view 
        //this is an associative array 
        $data = [
            "title" => "Login page",
            "username" => "",
            "password" => "",
            "usernameError" => "",
            "passwordError" => ""
        ];

        //Check for POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //Sanitize post data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                "username" => trim($_POST["username"]),
                "password" => trim($_POST["password"]),
                "usernameError" => "",
                "passwordError" => ""
            ];

            //Validate username
            if (empty($data["username"])) {
                $data["usernameError"] = "Please enter a username";
            }

            //Validate password
            if (empty($data["password"])) {
                $data["passwordError"] = "Please enter a password";
            }

            //Check if all the errors are empty
            if (empty($data["usernameError"]) && empty($data["passwordError"])) {
                $loggedInUser = $this->userModel->login($data["username"], $data["password"]);
                if ($loggedInUser) {
                    $this->createUserSession($loggedInUser);
                } else {
                    $data["passwordError"] = "Password or username is incorrect. Please try again.";
                    $this->view("users/login", $data);
                }
            }
        } else {
            $data = [
                "username" => "",
                "password" => "",
                "usernameError" => "",
                "passwordError" => ""
            ];
        }
        //define the view by saying that this pointer of view search for a file inside the folder user/login and also pass in the data array $data
        $this->view("users/login", $data);
    }
    public function createUserSession($user)
    {
        $_SESSION["user_id"] = $user->id;
        $_SESSION["username"] = $user->username;
        $_SESSION["email"] = $user->email;
        header("location:" . URLROOT . "/pages/index");
    }
    public function logout()
    {
        unset($_SESSION["user_id"]);
        unset($_SESSION["username"]);
        unset($_SESSION["email"]);
        header("location:" . URLROOT . "/users/login");
    }
}