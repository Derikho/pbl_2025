<?php
class GuestBook {
    private $conn;
    private $table_name = "guest_books";

    public $guest_id;
    public $full_name;
    public $institution;
    public $email;
    public $phone_number;
    public $message;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (full_name, institution, email, phone_number, message) 
                  VALUES (:name, :inst, :email, :phone, :msg)";

        $stmt = $this->conn->prepare($query);

        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->institution = htmlspecialchars(strip_tags($this->institution));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
        $this->message = htmlspecialchars(strip_tags($this->message));

        $stmt->bindParam(':name', $this->full_name);
        $stmt->bindParam(':inst', $this->institution);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone_number);
        $stmt->bindParam(':msg', $this->message);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE guest_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->guest_id);
        if($stmt->execute()) return true;
        return false;
    }
    
   public function countMessages() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>