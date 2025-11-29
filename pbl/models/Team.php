<?php
class Team {
    private $conn;
    private $table_name = "team_members";

    // Properti PHP
    public $id;
    public $name;
    public $position;
    public $email;
    public $phone;
    public $bio;
    public $photo;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT 
                    member_id as id,
                    name,
                    position,
                    public_email as email,
                    phone_number as phone,
                    bio,
                    photo_url as photo,
                    status,
                    created_at
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, position, public_email, phone_number, bio, photo_url, status) 
                  VALUES (:name, :position, :email, :phone, :bio, :photo, :status)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->bio = htmlspecialchars(strip_tags($this->bio));
        $this->photo = htmlspecialchars(strip_tags($this->photo));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind Parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':bio', $this->bio);
        $stmt->bindParam(':photo', $this->photo);
        $stmt->bindParam(':status', $this->status);

        try {
            if($stmt->execute()) {
                return true;
            }
            $errorInfo = $stmt->errorInfo();
            error_log("Team Create Error: " . print_r($errorInfo, true));
            return false;
        } catch(PDOException $e) {
            error_log("Team Create Exception: " . $e->getMessage());
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      position = :position, 
                      public_email = :email, 
                      phone_number = :phone, 
                      bio = :bio, 
                      photo_url = :photo, 
                      status = :status 
                  WHERE member_id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->bio = htmlspecialchars(strip_tags($this->bio));
        $this->photo = htmlspecialchars(strip_tags($this->photo));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind Parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':bio', $this->bio);
        $stmt->bindParam(':photo', $this->photo);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            $errorInfo = $stmt->errorInfo();
            error_log("Team Update Error: " . print_r($errorInfo, true));
            return false;
        } catch(PDOException $e) {
            error_log("Team Update Exception: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE member_id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Team Delete Exception: " . $e->getMessage());
            return false;
        }
    }
}
?>