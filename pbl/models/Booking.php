<?php
class Booking {
    private $conn;
    private $table_name = "loans";

    public $id;
    public $borrower_name;
    public $borrower_contact;
    public $borrower_email;
    public $institution;
    public $asset_id;
    public $qty;
    public $start_time;
    public $end_time;
    public $status;
    public $admin_note;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllBookings() {
        $query = "SELECT l.loan_id as id, 
                         l.borrower_name, 
                         l.borrower_email,
                         l.borrower_contact,
                         l.institution,
                         l.start_time as start_date, 
                         l.end_time as end_date, 
                         l.status,
                         l.qty,
                         l.admin_note,
                         l.created_at,
                         a.name as item_name,
                         a.category as booking_type,
                         a.available_quantity
                  FROM " . $this->table_name . " l
                  JOIN assets a ON l.asset_id = a.asset_id
                  ORDER BY l.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getTotalBookings() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getBookingsCountByStatus($status) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getActiveBookings() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status IN ('pending', 'approved')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function updateStatus($id, $status, $admin_note = '') {
        $query_get = "SELECT asset_id, qty, status FROM " . $this->table_name . " WHERE loan_id = :id";
        $stmt_get = $this->conn->prepare($query_get);
        $stmt_get->bindParam(':id', $id);
        $stmt_get->execute();
        $bookingData = $stmt_get->fetch(PDO::FETCH_ASSOC);

        if (!$bookingData) return false;

        include_once 'models/Asset.php';
        $asset_model = new Asset($this->conn); 
        
        if ($status == 'approved' && $bookingData['status'] == 'pending') {
            $asset_model->updateStock($bookingData['asset_id'], $bookingData['qty'], 'decrease');
        }
        else if (($status == 'returned' || $status == 'rejected') && $bookingData['status'] == 'approved') {
            $asset_model->updateStock($bookingData['asset_id'], $bookingData['qty'], 'increase');
        }
        else if ($status == 'rejected' && $bookingData['status'] == 'pending') {
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, admin_note = :admin_note 
                  WHERE loan_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':admin_note', $admin_note);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE loan_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
    
    public function getRoomAvailabilityStatus() {
        $query = "SELECT COUNT(*) as available FROM assets WHERE category = 'room' AND available_quantity > 0 AND is_active = true";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['available'] > 0;
    }
}
?>