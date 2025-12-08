<?php
class Partner {
    private $conn;
    private $table_name = "partners";

    public $id;
    public $name;
    public $description;
    public $website;
    public $logo;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT partner_id as id, name, description, 
                         website_url as website, logo_url as logo, 
                         status, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, website_url, logo_url, status) 
                  VALUES (:name, :description, :website, :logo, :status)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->website = htmlspecialchars(strip_tags($this->website));
        $this->logo = htmlspecialchars(strip_tags($this->logo));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':website', $this->website);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':status', $this->status);

        // Execute dengan error handling yang lebih baik
        try {
            if($stmt->execute()) {
                return true;
            }
            // Jika execute() return false
            $errorInfo = $stmt->errorInfo();
            error_log("Partner Create Error: " . print_r($errorInfo, true));
            return false;
        } catch(PDOException $e) {
            // Log error untuk debugging
            error_log("Partner Create Exception: " . $e->getMessage());
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      description = :description, 
                      website_url = :website, 
                      logo_url = :logo, 
                      status = :status 
                  WHERE partner_id = :id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->website = htmlspecialchars(strip_tags($this->website));
        $this->logo = htmlspecialchars(strip_tags($this->logo));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':website', $this->website);
        $stmt->bindParam(':logo', $this->logo);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            $errorInfo = $stmt->errorInfo();
            error_log("Partner Update Error: " . print_r($errorInfo, true));
            return false;
        } catch(PDOException $e) {
            error_log("Partner Update Exception: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE partner_id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Partner Delete Exception: " . $e->getMessage());
            return false;
        }
    }
    
}
?>