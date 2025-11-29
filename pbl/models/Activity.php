<?php
class Activity {
    private $conn;
    private $table_name = "activities"; // Sesuai tabel baru di SQL

    // Properti sesuai kolom tabel activities
    public $activity_id;
    public $activity_type;
    public $title;
    public $description;
    public $user_id;
    public $activity_date;
    public $location;
    public $status;
    public $created_at;
    public $updated_at;

    // Properti tambahan untuk join (nama user)
    public $username;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. READ (Ambil Data)
    public function read() {
        // Join dengan tabel users untuk mengambil nama pembuat activity
        $query = "SELECT a.*, u.username 
                  FROM " . $this->table_name . " a
                  LEFT JOIN users u ON a.user_id = u.user_id
                  ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. CREATE (Tambah Data)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (activity_type, title, description, user_id, activity_date, location, status) 
                  VALUES (:type, :title, :desc, :uid, :date, :loc, :status)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->activity_type = htmlspecialchars(strip_tags($this->activity_type));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->activity_date = htmlspecialchars(strip_tags($this->activity_date));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind
        $stmt->bindParam(':type', $this->activity_type);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':uid', $this->user_id);
        $stmt->bindParam(':date', $this->activity_date);
        $stmt->bindParam(':loc', $this->location);
        $stmt->bindParam(':status', $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 3. UPDATE (Edit Data)
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET activity_type = :type,
                      title = :title, 
                      description = :desc, 
                      activity_date = :date, 
                      location = :loc, 
                      status = :status,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE activity_id = :id";

        $stmt = $this->conn->prepare($query);

        // Bind
        $stmt->bindParam(':type', $this->activity_type);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':date', $this->activity_date);
        $stmt->bindParam(':loc', $this->location);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->activity_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 4. DELETE (Hapus Data)
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE activity_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->activity_id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getTotalActivities() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getCountByStatus($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>