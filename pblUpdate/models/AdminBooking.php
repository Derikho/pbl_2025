<?php
class AdminBooking {
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $user_id;
    public $booking_type;
    public $item_name;
    public $start_date;
    public $end_date;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllBookings() {
        $query = "SELECT b.*, u.username as user_name, u.email as user_email 
                  FROM " . $this->table_name . " b 
                  LEFT JOIN users u ON b.user_id = u.id 
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get bookings count by status
    public function getBookingsCountByStatus($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Get total bookings count
    public function getTotalBookings() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Update booking status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get bookings by date range
    public function getBookingsByDateRange($start_date, $end_date) {
        $query = "SELECT b.*, u.username as user_name, u.email as user_email 
                  FROM " . $this->table_name . " b 
                  LEFT JOIN users u ON b.user_id = u.id 
                  WHERE b.start_date BETWEEN ? AND ? 
                  ORDER BY b.start_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $start_date);
        $stmt->bindParam(2, $end_date);
        $stmt->execute();
        return $stmt;
    }

    // Get equipment usage statistics
    public function getEquipmentUsage() {
        $query = "SELECT 
                    item_name,
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_bookings,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings
                  FROM " . $this->table_name . " 
                  WHERE booking_type = 'peralatan' 
                  GROUP BY item_name 
                  ORDER BY total_bookings DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get room availability status
    public function getRoomAvailabilityStatus() {
        $query = "SELECT 
                    item_name,
                    COUNT(*) as active_bookings
                  FROM " . $this->table_name . " 
                  WHERE booking_type = 'ruangan' 
                  AND status IN ('approved', 'active')
                  AND start_date <= NOW() 
                  AND end_date >= NOW()
                  GROUP BY item_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Delete booking
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get booking details by ID
    public function getBookingById($id) {
        $query = "SELECT b.*, u.username as user_name, u.email as user_email, u.phone as user_phone 
                  FROM " . $this->table_name . " b 
                  LEFT JOIN users u ON b.user_id = u.id 
                  WHERE b.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>