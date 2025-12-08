<?php
class Team {
    private $conn;
    private $table_name = "team_members";

    public $id;
    public $name;
    public $position;
    public $email;
    public $phone;
    public $bio;
    public $photo;
    public $status;
    public $social_links; 
    public $profile_details; // Properti Baru
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
                    social_links,
                    profile_details, -- Ambil kolom ini
                    created_at
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        // Tambahkan profile_details ke query
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, position, public_email, phone_number, bio, photo_url, status, social_links, profile_details) 
                  VALUES (:name, :position, :email, :phone, :bio, :photo, :status, :social_links, :profile_details)";

        $stmt = $this->conn->prepare($query);

        // Sanitize basic fields
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->bio = htmlspecialchars(strip_tags($this->bio));
        $this->photo = htmlspecialchars(strip_tags($this->photo));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Encode JSON arrays
        if(is_array($this->social_links)) {
            $this->social_links = json_encode($this->social_links);
        }
        if(is_array($this->profile_details)) {
            $this->profile_details = json_encode($this->profile_details);
        }

        // Bind
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':bio', $this->bio);
        $stmt->bindParam(':photo', $this->photo);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':social_links', $this->social_links);
        $stmt->bindParam(':profile_details', $this->profile_details);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        // Tambahkan profile_details ke query update
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      position = :position, 
                      public_email = :email, 
                      phone_number = :phone, 
                      bio = :bio, 
                      photo_url = :photo, 
                      status = :status,
                      social_links = :social_links,
                      profile_details = :profile_details
                  WHERE member_id = :id";

        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->position = htmlspecialchars(strip_tags($this->position));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->bio = htmlspecialchars(strip_tags($this->bio));
        $this->photo = htmlspecialchars(strip_tags($this->photo));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        if(is_array($this->social_links)) {
            $this->social_links = json_encode($this->social_links);
        }
        if(is_array($this->profile_details)) {
            $this->profile_details = json_encode($this->profile_details);
        }

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':position', $this->position);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':bio', $this->bio);
        $stmt->bindParam(':photo', $this->photo);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':social_links', $this->social_links);
        $stmt->bindParam(':profile_details', $this->profile_details);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE member_id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>