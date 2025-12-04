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
    public $identification_number;
    public $institution;
    public $student_type;
    public $is_active;
    public $created_at;

    public $error_message; // Variabel penampung pesan error

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- FUNGSI VALIDASI KHUSUS ---
    private function validateInput($username, $id_number, $exclude_id = null) {
        
        // 1. CEK USERNAME (Wajib Unik)
        $query_user = "SELECT user_id FROM " . $this->table_name . " 
                       WHERE LOWER(username) = LOWER(:username)";
        if($exclude_id) {
            $query_user .= " AND user_id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query_user);
        $stmt->bindParam(':username', $username);
        if($exclude_id) $stmt->bindParam(':exclude_id', $exclude_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $this->error_message = "Username '$username' sudah digunakan user lain.";
            return false;
        }

        // 2. CEK NOMOR INDUK (Wajib Unik HANYA JIKA DIISI)
        if (!empty($id_number)) {
            $query_id = "SELECT user_id FROM " . $this->table_name . " 
                         WHERE identification_number = :id_number";
            if($exclude_id) {
                $query_id .= " AND user_id != :exclude_id";
            }

            $stmt = $this->conn->prepare($query_id);
            $stmt->bindParam(':id_number', $id_number);
            if($exclude_id) $stmt->bindParam(':exclude_id', $exclude_id);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $this->error_message = "Nomor Induk '$id_number' sudah terdaftar.";
                return false;
            }
        }

        return true; // Lolos Validasi
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        // 1. Sanitasi & Trim
        $this->username = htmlspecialchars(strip_tags(trim($this->username ?? '')));
        $this->full_name = htmlspecialchars(strip_tags(trim($this->full_name ?? '')));
        $this->identification_number = htmlspecialchars(strip_tags(trim($this->identification_number ?? '')));
        
        $id_number_val = !empty($this->identification_number) ? $this->identification_number : null;

        // --- VALIDASI PANJANG KARAKTER (BARU) ---
        if (strlen($this->username) > 50) {
            $this->error_message = "Gagal: Username terlalu panjang (Maksimal 50 karakter).";
            return false;
        }
        if (strlen($this->full_name) > 50) {
            $this->error_message = "Gagal: Nama Lengkap terlalu panjang (Maksimal 50 karakter).";
            return false;
        }
        if (!empty($this->identification_number) && strlen($this->identification_number) > 50) {
            $this->error_message = "Gagal: Nomor Induk terlalu panjang (Maksimal 50 karakter).";
            return false;
        }

        // 2. VALIDASI KEUNIKAN (Username/ID kembar)
        if(!$this->validateInput($this->username, $this->identification_number)) {
            return false; 
        }

        // 3. Insert Database
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password_hash, full_name, identification_number, institution, email, role, student_type, is_active) 
                  VALUES (:username, :password, :full_name, :id_number, :institution, :email, :role, :st_type, :active)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':id_number', $id_number_val);
        $stmt->bindParam(':institution', $this->institution);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':st_type', $this->student_type);
        $stmt->bindParam(':active', $this->is_active, PDO::PARAM_BOOL);

        try {
            if($stmt->execute()) { return true; }
        } catch(PDOException $e) {
            $this->error_message = "Database Error: " . $e->getMessage();
            return false;
        }
        return false;
    }

    public function update() {
        $this->id = (int)$this->id;
        $this->username = htmlspecialchars(strip_tags(trim($this->username ?? '')));
        $this->full_name = htmlspecialchars(strip_tags(trim($this->full_name ?? ''))); // Tambahkan ini
        $this->identification_number = htmlspecialchars(strip_tags(trim($this->identification_number ?? '')));

        $id_number_val = !empty($this->identification_number) ? $this->identification_number : null;

        // --- VALIDASI PANJANG KARAKTER (BARU) ---
        if (strlen($this->username) > 50) {
            $this->error_message = "Gagal: Username terlalu panjang (Maksimal 50 karakter).";
            return false;
        }
        if (strlen($this->full_name) > 50) {
            $this->error_message = "Gagal: Nama Lengkap terlalu panjang (Maksimal 50 karakter).";
            return false;
        }
        if (!empty($this->identification_number) && strlen($this->identification_number) > 50) {
            $this->error_message = "Gagal: Nomor Induk terlalu panjang (Maksimal 50 karakter).";
            return false;
        }

        // 1. VALIDASI KEUNIKAN
        if(!$this->validateInput($this->username, $this->identification_number, $this->id)) {
            return false;
        }

        // 2. Update Query
        $pass_set = !empty($this->password) ? ", password_hash = :password" : "";
        
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username,
                      full_name = :full_name,
                      identification_number = :id_number,
                      institution = :institution,
                      email = :email,
                      role = :role,
                      student_type = :st_type,
                      is_active = :active
                      " . $pass_set . "
                  WHERE user_id = :id";

        $stmt = $this->conn->prepare($query);
        // ... (Binding params sama seperti sebelumnya) ...
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':id_number', $id_number_val);
        $stmt->bindParam(':institution', $this->institution);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':st_type', $this->student_type);
        $stmt->bindParam(':active', $this->is_active, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $this->id);
        
        if(!empty($this->password)){
            $stmt->bindParam(':password', $this->password);
        }

        try {
            if($stmt->execute()) { return true; }
        } catch(PDOException $e) {
            $this->error_message = "Database Error: " . $e->getMessage();
            return false;
        }
        return false;
    }
    
    // ... sisa function lain (delete, read, stats) tetap sama ...
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = (int)$this->id;
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
    
    public function getUserStats() {
        $query = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN role = 'admin' THEN 1 END) as total_admins,
                    COUNT(CASE WHEN role = 'dosen' THEN 1 END) as total_dosen,
                    COUNT(CASE WHEN role = 'member' THEN 1 END) as total_members,
                    COUNT(CASE WHEN is_active = true THEN 1 END) as active_users
                  FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>