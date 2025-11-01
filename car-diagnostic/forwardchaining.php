<?php
// ForwardChaining.php - Forward Chaining Expert System Engine
require_once 'config.php';

class ForwardChaining {
    private $db;
    private $selectedSymptoms = [];
    private $results = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Set gejala yang dipilih user
     */
    public function setSymptoms($symptomIds) {
        $this->selectedSymptoms = array_map('intval', $symptomIds);
        return $this;
    }
    
    /**
     * Proses diagnosa menggunakan Forward Chaining
     */
    public function diagnose() {
        if (empty($this->selectedSymptoms)) {
            return [
                'success' => false,
                'message' => 'Tidak ada gejala yang dipilih'
            ];
        }
        
        // Ambil semua kemungkinan kerusakan
        $damages = $this->getAllDamages();
        
        if (empty($damages)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data kerusakan dalam sistem'
            ];
        }
        
        $results = [];
        
        // Proses setiap kerusakan
        foreach ($damages as $damage) {
            $analysis = $this->analyzeDamage($damage['damage_id']);
            
            // Hanya masukkan hasil jika confidence >= 30%
            if ($analysis['confidence'] >= 30) {
                $results[] = [
                    'damage_id' => $damage['damage_id'],
                    'damage_code' => $damage['damage_code'],
                    'damage_name' => $damage['damage_name'],
                    'category' => $damage['category_name'],
                    'description' => $damage['description'],
                    'solution' => $damage['solution'],
                    'cost_min' => $damage['estimated_cost_min'],
                    'cost_max' => $damage['estimated_cost_max'],
                    'severity' => $damage['severity_level'],
                    'matched_symptoms' => $analysis['matched_symptoms'],
                    'total_symptoms' => $analysis['total_symptoms'],
                    'confidence' => $analysis['confidence'],
                    'matched_symptom_details' => $analysis['symptom_details']
                ];
            }
        }
        
        // Urutkan berdasarkan confidence tertinggi
        usort($results, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        $this->results = $results;
        
        if (empty($results)) {
            return [
                'success' => false,
                'message' => 'Tidak ditemukan kerusakan yang cocok dengan gejala yang dipilih. Silakan konsultasi dengan mekanik profesional.'
            ];
        }
        
        return [
            'success' => true,
            'results' => $results,
            'total_results' => count($results),
            'primary_result' => $results[0]
        ];
    }
    
    /**
     * Analisa kerusakan berdasarkan gejala yang dipilih
     */
    private function analyzeDamage($damageId) {
        $sql = "SELECT r.symptom_id, r.confidence_factor, r.weight, r.is_primary,
                       s.symptom_name, s.symptom_code
                FROM rules r
                JOIN symptoms s ON r.symptom_id = s.symptom_id
                WHERE r.damage_id = ?
                ORDER BY r.weight DESC";
        
        $rules = $this->db->fetchAll($sql, [$damageId]);
        
        if (empty($rules)) {
            return [
                'matched_symptoms' => 0,
                'total_symptoms' => 0,
                'confidence' => 0,
                'symptom_details' => []
            ];
        }
        
        $totalSymptoms = count($rules);
        $matchedSymptoms = 0;
        $totalConfidence = 0;
        $totalWeight = 0;
        $symptomDetails = [];
        
        // Hitung gejala yang cocok
        foreach ($rules as $rule) {
            if (in_array($rule['symptom_id'], $this->selectedSymptoms)) {
                $matchedSymptoms++;
                $totalConfidence += $rule['confidence_factor'] * $rule['weight'];
                $totalWeight += $rule['weight'];
                
                $symptomDetails[] = [
                    'symptom_code' => $rule['symptom_code'],
                    'symptom_name' => $rule['symptom_name'],
                    'confidence_factor' => $rule['confidence_factor'],
                    'weight' => $rule['weight'],
                    'is_primary' => $rule['is_primary']
                ];
            }
        }
        
        // Hitung confidence berdasarkan weighted average
        $confidence = 0;
        if ($totalWeight > 0) {
            $weightedAvg = $totalConfidence / $totalWeight;
            $matchRatio = $matchedSymptoms / $totalSymptoms;
            $confidence = $weightedAvg * $matchRatio * 100;
        }
        
        return [
            'matched_symptoms' => $matchedSymptoms,
            'total_symptoms' => $totalSymptoms,
            'confidence' => round($confidence, 2),
            'symptom_details' => $symptomDetails
        ];
    }
    
    /**
     * Ambil semua kerusakan dengan kategorinya
     */
    private function getAllDamages() {
        $sql = "SELECT d.*, dc.category_name
                FROM damages d
                LEFT JOIN damage_categories dc ON d.category_id = dc.category_id
                WHERE d.damage_id IS NOT NULL
                ORDER BY d.damage_code";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Simpan hasil diagnosa ke database
     */
    public function saveToDatabase($userId, $vehicleData) {
        if (empty($this->results)) {
            return [
                'success' => false,
                'message' => 'Tidak ada hasil diagnosa untuk disimpan'
            ];
        }
        
        try {
            $this->db->beginTransaction();
            
            $primaryResult = $this->results[0];
            
            $diagnosisSql = "INSERT INTO diagnoses 
                            (user_id, vehicle_brand, vehicle_model, vehicle_year, 
                             total_symptoms, confidence_percentage, status) 
                            VALUES (?, ?, ?, ?, ?, ?, 'completed')";
            
            $this->db->query($diagnosisSql, [
                $userId,
                $vehicleData['brand'] ?? null,
                $vehicleData['model'] ?? null,
                $vehicleData['year'] ?? null,
                count($this->selectedSymptoms),
                $primaryResult['confidence']
            ]);
            
            $diagnosisId = $this->db->lastInsertId();
            
            // Simpan gejala yang dipilih
            $symptomSql = "INSERT INTO diagnosis_symptoms (diagnosis_id, symptom_id) VALUES (?, ?)";
            foreach ($this->selectedSymptoms as $symptomId) {
                $this->db->query($symptomSql, [$diagnosisId, $symptomId]);
            }
            
            // Simpan semua hasil diagnosa
            $resultSql = "INSERT INTO diagnosis_results 
                         (diagnosis_id, damage_id, matched_symptoms, total_symptoms_required, 
                          confidence_score, is_primary_result) 
                         VALUES (?, ?, ?, ?, ?, ?)";
            
            foreach ($this->results as $index => $result) {
                $this->db->query($resultSql, [
                    $diagnosisId,
                    $result['damage_id'],
                    $result['matched_symptoms'],
                    $result['total_symptoms'],
                    $result['confidence'],
                    $index === 0 ? 1 : 0
                ]);
            }
            
            logActivity($userId, 'diagnosis_completed', "Diagnosis completed for {$vehicleData['brand']} {$vehicleData['model']}");
            
            $this->db->commit();
            
            return [
                'success' => true,
                'diagnosis_id' => $diagnosisId,
                'message' => 'Hasil diagnosa berhasil disimpan'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error saving diagnosis: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal menyimpan hasil diagnosa: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Ambil detail diagnosa berdasarkan ID
     */
    public static function getDiagnosisDetail($diagnosisId) {
        $db = Database::getInstance();
        
        $diagnosis = $db->fetchOne(
            "SELECT d.*, u.username, u.full_name 
             FROM diagnoses d
             JOIN users u ON d.user_id = u.user_id
             WHERE d.diagnosis_id = ?",
            [$diagnosisId]
        );
        
        if (!$diagnosis) {
            return null;
        }
        
        $symptoms = $db->fetchAll(
            "SELECT s.symptom_code, s.symptom_name
             FROM diagnosis_symptoms ds
             JOIN symptoms s ON ds.symptom_id = s.symptom_id
             WHERE ds.diagnosis_id = ?",
            [$diagnosisId]
        );
        
        $results = $db->fetchAll(
            "SELECT dr.*, d.damage_code, d.damage_name, d.description, 
                    d.solution, d.estimated_cost_min, d.estimated_cost_max,
                    d.severity_level, dc.category_name
             FROM diagnosis_results dr
             JOIN damages d ON dr.damage_id = d.damage_id
             LEFT JOIN damage_categories dc ON d.category_id = dc.category_id
             WHERE dr.diagnosis_id = ?
             ORDER BY dr.confidence_score DESC",
            [$diagnosisId]
        );
        
        return [
            'diagnosis' => $diagnosis,
            'symptoms' => $symptoms,
            'results' => $results
        ];
    }
    
    /**
     * Ambil statistik diagnosa
     */
    public static function getStatistics() {
        $db = Database::getInstance();
        
        $stats = [];
        
        $stats['total_diagnoses'] = $db->fetchOne("SELECT COUNT(*) as count FROM diagnoses")['count'];
        $stats['total_users'] = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'user'")['count'];
        
        $stats['most_common_damage'] = $db->fetchOne(
            "SELECT d.damage_name, COUNT(*) as count
             FROM diagnosis_results dr
             JOIN damages d ON dr.damage_id = d.damage_id
             WHERE dr.is_primary_result = 1
             GROUP BY dr.damage_id
             ORDER BY count DESC
             LIMIT 1"
        );
        
        $stats['avg_confidence'] = $db->fetchOne("SELECT AVG(confidence_percentage) as avg FROM diagnoses")['avg'];
        
        return $stats;
    }
    
    /**
     * Ambil semua gejala dikelompokkan per kategori
     */
    public static function getAllSymptomsGrouped() {
        $db = Database::getInstance();
        
        $symptoms = $db->fetchAll(
            "SELECT symptom_id, symptom_code, symptom_name, 
                    symptom_question, category
             FROM symptoms
             ORDER BY category, symptom_code"
        );
        
        $grouped = [];
        foreach ($symptoms as $symptom) {
            $category = $symptom['category'] ?? 'Lainnya';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $symptom;
        }
        
        return $grouped;
    }
}
?>