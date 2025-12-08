<?php
class Activity {
    private $conn;
    private $table_name = "activities"; 
    
    // Properti tabel
    public $activity_id;
    public $activity_type;
    public $title;
    public $description;
    public $user_id;
    public $activity_date;
    public $location;
    public $status;
    public $link; // Link kegiatan / tautan referensi
    public $created_at;
    public $updated_at;

    public $username; // Username pembuat kegiatan

    public function __construct($db) {
        $this->conn = $db;
    }

    // --------------------------
    // AMBIL SEMUA DATA KEGIATAN
    // --------------------------
    public function read() {
        $query = "SELECT a.*, u.username 
                  FROM " . $this->table_name . " a
                  LEFT JOIN users u ON a.user_id = u.user_id
                  ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // --------------------------
    // TAMBAH KEGIATAN BARU
    // --------------------------
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (activity_type, title, description, user_id, activity_date, location, status, link) 
                  VALUES (:type, :title, :desc, :uid, :date, :loc, :status, :link)";

        $stmt = $this->conn->prepare($query);

        // Sanitasi input
        $this->activity_type  = htmlspecialchars(strip_tags($this->activity_type));
        $this->title          = htmlspecialchars(strip_tags($this->title));
        $this->description    = htmlspecialchars(strip_tags($this->description));
        $this->user_id        = htmlspecialchars(strip_tags($this->user_id));
        $this->activity_date  = htmlspecialchars(strip_tags($this->activity_date));
        $this->location       = htmlspecialchars(strip_tags($this->location));
        $this->status         = htmlspecialchars(strip_tags($this->status));
        $this->link           = htmlspecialchars(strip_tags($this->link));

        // Bind parameter
        $stmt->bindParam(':type', $this->activity_type);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':uid', $this->user_id);
        $stmt->bindParam(':date', $this->activity_date);
        $stmt->bindParam(':loc', $this->location);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':link', $this->link);

        return $stmt->execute();
    }

    // --------------------------
    // UPDATE DATA KEGIATAN
    // --------------------------
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET activity_type = :type,
                      title = :title, 
                      description = :desc, 
                      activity_date = :date, 
                      location = :loc, 
                      status = :status,
                      link = :link,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE activity_id = :id";

        $stmt = $this->conn->prepare($query);

        // Bind data yang akan diubah
        $stmt->bindParam(':type', $this->activity_type);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':date', $this->activity_date);
        $stmt->bindParam(':loc', $this->location);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':link', $this->link);
        $stmt->bindParam(':id', $this->activity_id);

        return $stmt->execute();
    }

    // --------------------------
    // HAPUS KEGIATAN
    // --------------------------
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE activity_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->activity_id);
        return $stmt->execute();
    }

    // --------------------------
    // TOTAL KEGIATAN
    // --------------------------
    public function getTotalActivities() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // --------------------------
    // HITUNG KEGIATAN BERDASARKAN STATUS
    // --------------------------
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
