<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $role;
    public $full_name;
    public $nim;
    public $institution;
    public $student_type;
    public $is_active;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($this->password, $row['password_hash'])) {
                $this->id = $row['user_id'];
                $this->username = $row['username'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password_hash, full_name, nim, institution, email, role, student_type, is_active) 
                  VALUES (:username, :password, :full_name, :nim, :institution, :email, :role, :student_type, :is_active)";

        $stmt = $this->conn->prepare($query);

        // PERBAIKAN: Gunakan ?? '' agar tidak error saat nilai NULL
        $this->username = htmlspecialchars(strip_tags($this->username ?? ''));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name ?? ''));
        $this->nim = htmlspecialchars(strip_tags($this->nim ?? ''));
        $this->institution = htmlspecialchars(strip_tags($this->institution ?? ''));
        $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
        $this->role = htmlspecialchars(strip_tags($this->role ?? ''));
        $this->student_type = htmlspecialchars(strip_tags($this->student_type ?? ''));

        // Jika string kosong, kembalikan ke NULL agar database bersih (Opsional, tergantung kebijakan DB)
        // Di sini kita biarkan string kosong atau NULL sesuai kebutuhan binding
        $nimVal = empty($this->nim) ? null : $this->nim;
        $instVal = empty($this->institution) ? null : $this->institution;
        $stTypeVal = empty($this->student_type) ? null : $this->student_type;

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':nim', $nimVal);
        $stmt->bindParam(':institution', $instVal);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':student_type', $stTypeVal);
        $stmt->bindParam(':is_active', $this->is_active, PDO::PARAM_BOOL);

        if($stmt->execute()) { return true; }
        return false;
    }

    public function update() {
        if(!empty($this->password)){
            $query = "UPDATE " . $this->table_name . " 
                      SET username = :username,
                          password_hash = :password,
                          full_name = :full_name,
                          nim = :nim,
                          institution = :institution,
                          email = :email,
                          role = :role,
                          student_type = :student_type,
                          is_active = :is_active,
                          updated_at = CURRENT_TIMESTAMP
                      WHERE user_id = :id";
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET username = :username,
                          full_name = :full_name,
                          nim = :nim,
                          institution = :institution,
                          email = :email,
                          role = :role,
                          student_type = :student_type,
                          is_active = :is_active,
                          updated_at = CURRENT_TIMESTAMP
                      WHERE user_id = :id";
        }

        $stmt = $this->conn->prepare($query);

        // PERBAIKAN: Tambahkan ?? '' untuk mencegah error Deprecated di PHP 8.1+
        $this->id = (int)$this->id;
        $this->username = htmlspecialchars(strip_tags($this->username ?? ''));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name ?? ''));
        $this->nim = htmlspecialchars(strip_tags($this->nim ?? ''));
        $this->institution = htmlspecialchars(strip_tags($this->institution ?? ''));
        $this->email = htmlspecialchars(strip_tags($this->email ?? ''));
        $this->role = htmlspecialchars(strip_tags($this->role ?? ''));
        $this->student_type = htmlspecialchars(strip_tags($this->student_type ?? ''));

        // Handle Null values for DB
        $nimVal = empty($this->nim) ? null : $this->nim;
        $instVal = empty($this->institution) ? null : $this->institution;
        $stTypeVal = empty($this->student_type) ? null : $this->student_type;

        $stmt->bindParam(':username', $this->username);
        if(!empty($this->password)){
            $stmt->bindParam(':password', $this->password);
        }
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':nim', $nimVal);
        $stmt->bindParam(':institution', $instVal);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':student_type', $stTypeVal);
        $stmt->bindParam(':is_active', $this->is_active, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) { return true; }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = (int)$this->id;
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()) { return true; }
        return false;
    }
}
?>