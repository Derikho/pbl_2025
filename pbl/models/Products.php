<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $description;
    public $image_url;
    public $link_demo;
    public $price;
    // Category & Status dihapus dari properti jika DB tidak mendukung

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. READ (Hapus created_at, category, status dari query)
    public function read() {
        $query = "SELECT 
                    product_id as id,
                    name, 
                    description, 
                    image_url, 
                    link_demo, 
                    price
                  FROM " . $this->table_name . " 
                  ORDER BY product_id DESC"; // Ubah urutan jadi by ID karena created_at tidak ada

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, image_url, link_demo, price) 
                  VALUES (:name, :desc, :img, :link, :price)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->link_demo = htmlspecialchars(strip_tags($this->link_demo));
        $this->price = htmlspecialchars(strip_tags($this->price));

        // Bind
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':img', $this->image_url);
        $stmt->bindParam(':link', $this->link_demo);
        $stmt->bindParam(':price', $this->price);

        if($stmt->execute()) { return true; }
        return false;
    }

    // 3. UPDATE
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      description = :desc, 
                      image_url = :img, 
                      link_demo = :link,
                      price = :price
                  WHERE product_id = :id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->link_demo = htmlspecialchars(strip_tags($this->link_demo));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':img', $this->image_url);
        $stmt->bindParam(':link', $this->link_demo);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) { return true; }
        return false;
    }

    // 4. DELETE
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE product_id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()) { return true; }
        return false;
    }
}
?>