<?php
class Attendance {
    private $conn;
    private $table_name = "attendance_logs";

    public $id;
    public $user_id;
    public $date;
    public $check_in_time;
    public $check_out_time;
    public $photo_proof;
    public $location_note;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($date = null) {
        $query = "SELECT 
                    al.log_id as id,
                    al.user_id,
                    u.full_name as name,
                    u.nim,
                    u.institution as kelas,
                    al.date,
                    al.check_in_time,
                    al.check_out_time,
                    al.photo_proof,
                    al.location_note,
                    al.created_at
                  FROM " . $this->table_name . " al
                  LEFT JOIN users u ON al.user_id = u.user_id";
        
        if ($date) {
            $query .= " WHERE al.date = :date";
        }
        
        $query .= " ORDER BY al.date DESC, al.check_in_time DESC";

        $stmt = $this->conn->prepare($query);
        
        if ($date) {
            $stmt->bindParam(':date', $date);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, date, check_in_time, check_out_time, photo_proof, location_note) 
                  VALUES (:user_id, :date, :check_in_time, :check_out_time, :photo_proof, :location_note)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->user_id = (int)$this->user_id;
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->check_in_time = htmlspecialchars(strip_tags($this->check_in_time));
        $this->check_out_time = htmlspecialchars(strip_tags($this->check_out_time));
        $this->photo_proof = htmlspecialchars(strip_tags($this->photo_proof));
        $this->location_note = htmlspecialchars(strip_tags($this->location_note));

        // Bind Parameters
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':check_in_time', $this->check_in_time);
        $stmt->bindParam(':check_out_time', $this->check_out_time);
        $stmt->bindParam(':photo_proof', $this->photo_proof);
        $stmt->bindParam(':location_note', $this->location_note);

        try {
            if($stmt->execute()) {
                return true;
            }
            $errorInfo = $stmt->errorInfo();
            error_log("Attendance Create Error: " . print_r($errorInfo, true));
            return false;
        } catch(PDOException $e) {
            error_log("Attendance Create Exception: " . $e->getMessage());
            return false;
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET user_id = :user_id,
                      date = :date,
                      check_in_time = :check_in_time,
                      check_out_time = :check_out_time,
                      photo_proof = :photo_proof,
                      location_note = :location_note
                  WHERE log_id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id = (int)$this->id;
        $this->user_id = (int)$this->user_id;
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->check_in_time = htmlspecialchars(strip_tags($this->check_in_time));
        $this->check_out_time = htmlspecialchars(strip_tags($this->check_out_time));
        $this->photo_proof = htmlspecialchars(strip_tags($this->photo_proof));
        $this->location_note = htmlspecialchars(strip_tags($this->location_note));

        // Bind Parameters
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':date', $this->date);
        $stmt->bindParam(':check_in_time', $this->check_in_time);
        $stmt->bindParam(':check_out_time', $this->check_out_time);
        $stmt->bindParam(':photo_proof', $this->photo_proof);
        $stmt->bindParam(':location_note', $this->location_note);
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Attendance Update Exception: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE log_id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = (int)$this->id;
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Attendance Delete Exception: " . $e->getMessage());
            return false;
        }
    }

    public function getStats($date = null) {
        $today = $date ?? date('Y-m-d');
        
        $query = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN check_in_time <= '08:00:00' THEN 1 END) as tepat_waktu,
                    COUNT(CASE WHEN check_in_time > '08:00:00' THEN 1 END) as terlambat,
                    COUNT(CASE WHEN check_out_time IS NOT NULL THEN 1 END) as sudah_keluar,
                    COUNT(CASE WHEN check_out_time IS NULL THEN 1 END) as belum_keluar
                  FROM " . $this->table_name . "
                  WHERE date = :date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $today);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalToday() {
        $today = date('Y-m-d');
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE date = :today";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>