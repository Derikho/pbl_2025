<?php
class Absent {
    private $conn;
    private $table_name = "attendance_logs";

    public $log_id;
    public $user_id;
    public $date;
    public $check_in_time;
    public $check_out_time;
    public $location_note;
    public $photo_proof;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ============================================
    // CREATE ABSENSI (CHECK-IN)
    // ============================================
    public function create($user_id, $matakuliah, $keterangan, $catatan = '', $photo_path = '') {
        $today = date('Y-m-d');
        $check_in_time = date('H:i:s');
        
        // Cek apakah sudah absen hari ini
        if($this->hasAttendedToday($user_id)){
            return ['success' => false, 'message' => 'Anda sudah melakukan absensi hari ini!'];
        }
        
        // Format location note
        $location_note = "Mata Kuliah: {$matakuliah} | Keterangan: {$keterangan}";
        if(!empty($catatan)){
            $location_note .= " | Catatan: {$catatan}";
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, date, check_in_time, location_note, photo_proof, created_at) 
                  VALUES (:user_id, :date, :check_in_time, :location_note, :photo_proof, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":date", $today);
        $stmt->bindParam(":check_in_time", $check_in_time);
        $stmt->bindParam(":location_note", $location_note);
        $stmt->bindParam(":photo_proof", $photo_path);
        
        if($stmt->execute()){
            $this->log_id = $this->conn->lastInsertId();
            return [
                'success' => true, 
                'message' => 'Absensi berhasil dicatat!',
                'data' => [
                    'log_id' => $this->log_id,
                    'date' => $today,
                    'time' => $check_in_time,
                    'matakuliah' => $matakuliah
                ]
            ];
        }
        
        return ['success' => false, 'message' => 'Gagal menyimpan absensi.'];
    }

    // ============================================
    // CHECK-OUT (PULANG)
    // ============================================
    public function checkout($user_id) {
        $today = date('Y-m-d');
        $check_out_time = date('H:i:s');
        
        // Cek apakah sudah check-in hari ini
        $attendance = $this->getTodayAttendance($user_id);
        if(!$attendance){
            return ['success' => false, 'message' => 'Anda belum melakukan check-in hari ini!'];
        }
        
        // Cek apakah sudah check-out
        if(!empty($attendance['check_out_time'])){
            return ['success' => false, 'message' => 'Anda sudah melakukan check-out hari ini!'];
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET check_out_time = :check_out_time 
                  WHERE user_id = :user_id AND date = :date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":check_out_time", $check_out_time);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":date", $today);
        
        if($stmt->execute()){
            return [
                'success' => true, 
                'message' => 'Check-out berhasil! Selamat pulang.',
                'data' => [
                    'check_out_time' => $check_out_time,
                    'work_hours' => $this->calculateWorkHours($attendance['check_in_time'], $check_out_time)
                ]
            ];
        }
        
        return ['success' => false, 'message' => 'Gagal melakukan check-out.'];
    }

    // ============================================
    // GET ATTENDANCE HISTORY
    // ============================================
    public function getHistory($user_id, $limit = 10) {
        $query = "SELECT 
                    al.log_id,
                    al.date,
                    al.check_in_time,
                    al.check_out_time,
                    al.location_note,
                    al.photo_proof,
                    al.created_at,
                    u.full_name,
                    u.identification_number,
                    u.institution,
                    u.student_type
                  FROM " . $this->table_name . " al
                  LEFT JOIN users u ON al.user_id = u.user_id
                  WHERE al.user_id = :user_id 
                  ORDER BY al.date DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // GET TODAY'S ATTENDANCE STATUS
    // ============================================
    public function getTodayAttendance($user_id) {
        $today = date('Y-m-d');
        
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND date = :date 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':date', $today);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ============================================
    // CHECK IF ALREADY ATTENDED TODAY
    // ============================================
    public function hasAttendedToday($user_id) {
        $attendance = $this->getTodayAttendance($user_id);
        return $attendance !== false;
    }

    // ============================================
    // GET STATISTICS
    // ============================================
    public function getStats($user_id) {
        $query = "SELECT 
                    COUNT(*) as total_absensi,
                    SUM(CASE WHEN DATE(date) = CURRENT_DATE THEN 1 ELSE 0 END) as hari_ini,
                    SUM(CASE WHEN check_in_time <= '08:00:00' THEN 1 ELSE 0 END) as tepat_waktu,
                    SUM(CASE WHEN check_in_time > '08:00:00' THEN 1 ELSE 0 END) as terlambat,
                    SUM(CASE WHEN check_out_time IS NOT NULL THEN 1 ELSE 0 END) as sudah_pulang,
                    MIN(date) as pertama_absen,
                    MAX(date) as terakhir_absen
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Hitung rata-rata jam kerja
        $hours_query = "SELECT 
                          AVG(EXTRACT(EPOCH FROM (check_out_time::time - check_in_time::time)) / 3600) as avg_hours
                        FROM " . $this->table_name . " 
                        WHERE user_id = :user_id AND check_out_time IS NOT NULL";
        
        $hours_stmt = $this->conn->prepare($hours_query);
        $hours_stmt->bindParam(':user_id', $user_id);
        $hours_stmt->execute();
        $hours = $hours_stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['rata_jam_kerja'] = round($hours['avg_hours'] ?? 0, 1);
        
        return $stats;
    }

    // ============================================
    // GET MONTHLY REPORT
    // ============================================
    public function getMonthlyReport($user_id, $month = null, $year = null) {
        if($month === null) $month = date('m');
        if($year === null) $year = date('Y');
        
        $query = "SELECT 
                    date,
                    check_in_time,
                    check_out_time,
                    location_note,
                    EXTRACT(DAY FROM date) as hari,
                    TO_CHAR(date, 'Day') as nama_hari,
                    CASE 
                        WHEN check_in_time IS NULL THEN 'Tidak Hadir'
                        WHEN check_in_time <= '08:00:00' THEN 'Tepat Waktu'
                        ELSE 'Terlambat'
                    END as status_kehadiran,
                    EXTRACT(EPOCH FROM (check_out_time::time - check_in_time::time)) / 3600 as jam_kerja
                  FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                    AND EXTRACT(MONTH FROM date) = :month 
                    AND EXTRACT(YEAR FROM date) = :year 
                  ORDER BY date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // GET USER INFO
    // ============================================
    public function getUserInfo($user_id) {
        $query = "SELECT 
                    user_id,
                    username,
                    full_name,
                    identification_number,
                    institution,
                    email,
                    role,
                    student_type,
                    is_active
                  FROM users 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ============================================
    // HELPER FUNCTIONS
    // ============================================
    
    // Parse location note untuk mendapatkan mata kuliah
    public static function parseLocationNote($location_note) {
        $result = [
            'matakuliah' => '',
            'keterangan' => '',
            'catatan' => ''
        ];
        
        if(empty($location_note)) return $result;
        
        $parts = explode('|', $location_note);
        
        foreach($parts as $part){
            if(strpos($part, 'Mata Kuliah:') !== false){
                $result['matakuliah'] = trim(str_replace('Mata Kuliah:', '', $part));
            }
            if(strpos($part, 'Keterangan:') !== false){
                $result['keterangan'] = trim(str_replace('Keterangan:', '', $part));
            }
            if(strpos($part, 'Catatan:') !== false){
                $result['catatan'] = trim(str_replace('Catatan:', '', $part));
            }
        }
        
        return $result;
    }
    
    // Hitung jam kerja
    private function calculateWorkHours($check_in, $check_out) {
        if(empty($check_in) || empty($check_out)) return 0;
        
        $in = strtotime($check_in);
        $out = strtotime($check_out);
        
        $diff = $out - $in;
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        
        return "{$hours} jam {$minutes} menit";
    }
    
    // Get status kehadiran
    public static function getAttendanceStatus($check_in_time, $check_out_time = null) {
        if(empty($check_in_time)){
            return [
                'status' => 'Tidak Hadir',
                'badge' => 'danger',
                'icon' => 'fa-times-circle'
            ];
        }
        
        $is_late = strtotime($check_in_time) > strtotime('08:00:00');
        
        if(!empty($check_out_time)){
            return [
                'status' => $is_late ? 'Terlambat (Sudah Pulang)' : 'Tepat Waktu (Sudah Pulang)',
                'badge' => $is_late ? 'warning' : 'success',
                'icon' => $is_late ? 'fa-clock' : 'fa-check-circle'
            ];
        } else {
            return [
                'status' => $is_late ? 'Terlambat' : 'Tepat Waktu',
                'badge' => $is_late ? 'warning' : 'success',
                'icon' => $is_late ? 'fa-clock' : 'fa-check-circle'
            ];
        }
    }
    
    // Upload foto
    public function uploadPhoto($file, $user_id) {
        if($file['error'] != 0 || empty($file['name'])){
            return ['success' => false, 'message' => 'Tidak ada file yang diupload.'];
        }
        
        $targetDir = "uploads/attendance/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Generate unique filename
        $fileName = time() . '_' . $user_id . '_' . basename($file["name"]);
        $targetFilePath = $targetDir . $fileName;
        
        // Validasi file type
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        
        if(!in_array($imageFileType, $allowedTypes)){
            return ['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.'];
        }
        
        // Validasi ukuran (max 5MB)
        if($file["size"] > 5 * 1024 * 1024){
            return ['success' => false, 'message' => 'Ukuran file terlalu besar! Maksimal 5MB.'];
        }
        
        // Upload file
        if(move_uploaded_file($file["tmp_name"], $targetFilePath)){
            return [
                'success' => true, 
                'path' => $targetFilePath,
                'filename' => $fileName
            ];
        }
        
        return ['success' => false, 'message' => 'Gagal upload file.'];
    }
    
    // Get student type label
    public static function getStudentTypeLabel($student_type) {
        $labels = [
            'regular' => 'Reguler',
            'magang' => 'Magang',
            'pascasarjana' => 'Pascasarjana',
            'non-reguler' => 'Non-Reguler',
            'karyawan' => 'Karyawan',
            'internasional' => 'Internasional'
        ];
        
        if(empty($student_type)) {
            return 'Tidak Diketahui';
        }
        
        return $labels[$student_type] ?? ucfirst($student_type);
    }
}
?>