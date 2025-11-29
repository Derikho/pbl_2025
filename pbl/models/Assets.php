<?php
class Asset {
    private $conn;
    private $table_name = "assets";

    public $id;
    public $name;
    public $category;
    public $description;
    public $total_quantity;
    public $available_quantity;
    public $capacity;
    public $image_url;
    public $is_active;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($category = null) {
        $query = "SELECT asset_id as id, name, category, description, 
                         total_quantity, available_quantity, capacity, 
                         image_url, is_active, created_at 
                  FROM " . $this->table_name . " 
                  WHERE is_active = true";
        
        if ($category) {
            $query .= " AND category = :cat";
        }
        $query .= " ORDER BY category, name ASC";

        $stmt = $this->conn->prepare($query);
        if ($category) {
            $stmt->bindParam(':cat', $category);
        }
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, category, description, total_quantity, available_quantity, capacity, image_url, is_active) 
                  VALUES (:name, :category, :description, :total_quantity, :available_quantity, :capacity, :image_url, :is_active)";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->total_quantity = (int)$this->total_quantity;
        $this->available_quantity = (int)$this->available_quantity;
        $this->capacity = (int)$this->capacity;
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->is_active = (bool)$this->is_active;

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':total_quantity', $this->total_quantity);
        $stmt->bindParam(':available_quantity', $this->available_quantity);
        $stmt->bindParam(':capacity', $this->capacity);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':is_active', $this->is_active, PDO::PARAM_BOOL);

        try {
            if($stmt->execute()) {
                return true;
            }
            $errorInfo = $stmt->errorInfo();
            error_log("Asset Create Error: " . print_r($errorInfo, true));
            return false;
        } catch(PDOException $e) {
            error_log("Asset Create Exception: " . $e->getMessage());
            return false;
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name,
                      category = :category,
                      description = :description,
                      total_quantity = :total_quantity,
                      available_quantity = :available_quantity,
                      capacity = :capacity,
                      image_url = :image_url,
                      is_active = :is_active
                  WHERE asset_id = :id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->total_quantity = (int)$this->total_quantity;
        $this->available_quantity = (int)$this->available_quantity;
        $this->capacity = (int)$this->capacity;
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->is_active = (bool)$this->is_active;
        $this->id = (int)$this->id;

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':total_quantity', $this->total_quantity);
        $stmt->bindParam(':available_quantity', $this->available_quantity);
        $stmt->bindParam(':capacity', $this->capacity);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':is_active', $this->is_active, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Asset Update Exception: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET is_active = false WHERE asset_id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = (int)$this->id;
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Asset Delete Exception: " . $e->getMessage());
            return false;
        }
    }

    public function updateStock($id, $qty, $operation = 'decrease') {
        if ($operation == 'decrease') {
            $query = "UPDATE " . $this->table_name . " 
                      SET available_quantity = available_quantity - :qty 
                      WHERE asset_id = :id AND available_quantity >= :qty";
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET available_quantity = available_quantity + :qty 
                      WHERE asset_id = :id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':qty', $qty, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAssetStats() {
        $query = "SELECT 
                    COUNT(*) as total_assets,
                    SUM(CASE WHEN category = 'tool' THEN 1 ELSE 0 END) as total_tools,
                    SUM(CASE WHEN category = 'room' THEN 1 ELSE 0 END) as total_rooms,
                    SUM(total_quantity) as total_quantity,
                    SUM(available_quantity) as available_quantity
                  FROM " . $this->table_name . "
                  WHERE is_active = true";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>