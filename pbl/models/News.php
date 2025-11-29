<?php
class News {
    private $conn;
    private $table_name = "posts";

    public $id;
    public $title;
    public $slug;
    public $content;
    public $image_url;   
    public $status;
    public $category;    
    public $publish_date; 
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT 
                    post_id as id, 
                    title, 
                    slug,
                    content, 
                    thumbnail_url as image_url, 
                    status, 
                    category,
                    COALESCE(publish_date, created_at) as publish_date,
                    created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, slug, content, thumbnail_url, status, category, publish_date) 
                  VALUES (:title, :slug, :content, :image_url, :status, :category, :publish_date)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = $this->createSlug($this->title); 
        $this->content = $this->content; // Keep HTML for content
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->category = htmlspecialchars(strip_tags($this->category));
        
        // Set default publish date if empty
        if(empty($this->publish_date)) {
            $this->publish_date = date('Y-m-d H:i:s');
        }

        // Bind Parameters
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':image_url', $this->image_url); 
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':publish_date', $this->publish_date);

        try {
            if($stmt->execute()) {
                return true;
            }
            $errorInfo = $stmt->errorInfo();
            error_log("News Create Error: " . print_r($errorInfo, true));
            return false;
        } catch(PDOException $e) {
            error_log("News Create Exception: " . $e->getMessage());
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title = :title, 
                      slug = :slug,
                      content = :content, 
                      thumbnail_url = :image_url, 
                      status = :status,
                      category = :category,
                      publish_date = :publish_date
                  WHERE post_id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = $this->createSlug($this->title);
        $this->content = $this->content; // Keep HTML for content
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind Parameters
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':publish_date', $this->publish_date);
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            $errorInfo = $stmt->errorInfo();
            error_log("News Update Error: " . print_r($errorInfo, true));
            return false;
        } catch(PDOException $e) {
            error_log("News Update Exception: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE post_id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("News Delete Exception: " . $e->getMessage());
            return false;
        }
    }

    // Helper function to create URL-friendly slug
    private function createSlug($string) {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
?>