<?php
namespace app\models;

class User{
    private static $conn;
    private $id;
    private $firstname;
    private $lastname;
    private $email;
    private $role;
    private $created_at;
    private static $users = [];

    public function __construct($id, $firstname, $lastname, $email, $role, $created_at) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->role = $role;
        $this->created_at = $created_at;

        if (!self::userIdExists($id)) {
            self::$users[$id] = $this;
        }
    }

    public static function userIdExists($id): bool
    {
        return isset(self::$users[$id]);
    }

    public function getId() {
        return $this->id;
    }

    public function getFirstName():string {
        return $this->firstname;
    }

    public function setFirstName($firstname) {
        $this->firstname = $firstname;
    }

    public function getLastName():string {
        return $this->lastname;
    }

    public function setLastName($lastname) {
        $this->lastname = $lastname;
    }

    public function getEmail():string {
        return $this->email;
    }

    public function setEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->email = $email;
        }
        else {
            $this->email = null;
            echo "Email is not valid";
        }
    }

    public function getRole():string {
        return $this->role;
    }

    public function setRole($role) {
        $this->role = $role;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public static function validatePassword($password):bool {
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/';

        if (preg_match($pattern, $password)) {
            return true;
        } else {
            return false;
        }
    }

    public static function setConnection($conn){
        self::$conn = $conn;
    }

    private static function clearUsers() {
        self::$users = [];
    }

    public static function getUsers():array {
        return self::$users;
    }

    public static function getUserById($id): ?User {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = mysqli_prepare(self::$conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            return new User(
                $user['id'],
                $user['firstname'],
                $user['lastname'],
                $user['email'],
                $user['role'],
                $user['created_at']
            );
        }

        return null;
    }

    public static function loadUsers():void {
        $query = "SELECT * FROM users";
        $result = mysqli_query(self::$conn, $query);
        self::clearUsers();

        if (mysqli_num_rows($result) > 0) {
            $fetchedUsers = mysqli_fetch_all($result, MYSQLI_ASSOC);

            foreach ($fetchedUsers as $user) {
                new User(
                    $user['id'],
                    $user['firstname'],
                    $user['lastname'],
                    $user['email'],
                    $user['role'],
                    $user['created_at']
                );
            }
        }
    }

public static function isValidCredentials($email, $password) {
    global $conn;
    $sql = "SELECT * FROM Users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return new User(
                $user['id'],          
                $user['firstname'],   
                $user['lastname'],    
                $user['email'],      
                $user['role'],      
                $user['created_at'],
            );
        }
    }
    return false; 
}

public static function addUser($firstname, $lastname, $email, $role, $password):bool {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (firstname, lastname, email, role, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare(self::$conn, $query);
    mysqli_stmt_bind_param($stmt, 'sssss', $firstname, $lastname, $email, $role, $hashedPassword);

    if (mysqli_stmt_execute($stmt)) {
        new User(
            mysqli_insert_id(self::$conn),
            $firstname,
            $lastname,
            $email,
            $role,
            date('Y-m-d H:i:s')
        );

        return true;
    }

    return false;
}

public static function emailExist($email):bool {
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare(self::$conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        return true;
    }

    return false;
}
}