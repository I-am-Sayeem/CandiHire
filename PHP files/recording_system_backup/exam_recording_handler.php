<?php
// exam_recording_handler.php - Handles exam recording functionality
require_once 'Database.php';
require_once 'session_manager.php';

class ExamRecordingHandler {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            throw new Exception('Database connection not available');
        }
        $this->pdo = $pdo;
    }
    
    /**
     * Start recording session for an exam attempt
     */
    public function startRecording($attemptId, $candidateId, $examId) {
        try {
            $this->pdo->beginTransaction();
            
            // Check if recording already exists for this attempt
            $checkStmt = $this->pdo->prepare("
                SELECT RecordingID FROM exam_recordings 
                WHERE AttemptID = ? AND Status = 'recording'
            ");
            $checkStmt->execute([$attemptId]);
            
            if ($checkStmt->fetch()) {
                throw new Exception('Recording already in progress for this attempt');
            }
            
            // Create screen recording entry
            $screenFileName = 'screen_recording_' . $attemptId;
            $screenStmt = $this->pdo->prepare("
                INSERT INTO exam_recordings 
                (AttemptID, CandidateID, ExamID, RecordingType, RecordingData, FileName, FileSize, MimeType, Duration, Status) 
                VALUES (?, ?, ?, 'screen', '', ?, 0, 'video/webm', 0, 'recording')
            ");
            $screenStmt->execute([$attemptId, $candidateId, $examId, $screenFileName]);
            $screenRecordingId = $this->pdo->lastInsertId();
            
            // Create webcam recording entry
            $webcamFileName = 'webcam_recording_' . $attemptId;
            $webcamStmt = $this->pdo->prepare("
                INSERT INTO exam_recordings 
                (AttemptID, CandidateID, ExamID, RecordingType, RecordingData, FileName, FileSize, MimeType, Duration, Status) 
                VALUES (?, ?, ?, 'webcam', '', ?, 0, 'video/webm', 0, 'recording')
            ");
            $webcamStmt->execute([$attemptId, $candidateId, $examId, $webcamFileName]);
            $webcamRecordingId = $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'screen_recording_id' => $screenRecordingId,
                'webcam_recording_id' => $webcamRecordingId
            ];
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error starting recording: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Save recording data
     */
    public function saveRecordingData($recordingId, $recordingData, $fileName, $fileSize, $duration) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE exam_recordings 
                SET RecordingData = ?, FileName = ?, FileSize = ?, Duration = ?, 
                    EndTime = NOW(), Status = 'completed', UpdatedAt = NOW()
                WHERE RecordingID = ?
            ");
            
            $result = $stmt->execute([$recordingData, $fileName, $fileSize, $duration, $recordingId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Failed to update recording data'];
            }
            
        } catch (Exception $e) {
            error_log("Error saving recording data: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get recording data for viewing
     */
    public function getRecordingData($recordingId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT RecordingData, FileName, MimeType, Duration, RecordingType, Status
                FROM exam_recordings 
                WHERE RecordingID = ?
            ");
            $stmt->execute([$recordingId]);
            $recording = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($recording) {
                return [
                    'success' => true,
                    'data' => $recording
                ];
            } else {
                return ['success' => false, 'error' => 'Recording not found'];
            }
            
        } catch (Exception $e) {
            error_log("Error getting recording data: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get all recordings for an exam attempt
     */
    public function getAttemptRecordings($attemptId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT RecordingID, RecordingType, FileName, FileSize, Duration, 
                       StartTime, EndTime, Status
                FROM exam_recordings 
                WHERE AttemptID = ?
                ORDER BY RecordingType, StartTime
            ");
            $stmt->execute([$attemptId]);
            $recordings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'recordings' => $recordings
            ];
            
        } catch (Exception $e) {
            error_log("Error getting attempt recordings: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Stop recording and mark as completed
     */
    public function stopRecording($recordingId) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE exam_recordings 
                SET Status = 'completed', EndTime = NOW(), UpdatedAt = NOW()
                WHERE RecordingID = ? AND Status = 'recording'
            ");
            
            $result = $stmt->execute([$recordingId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Recording not found or already stopped'];
            }
            
        } catch (Exception $e) {
            error_log("Error stopping recording: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Delete recording data
     */
    public function deleteRecording($recordingId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM exam_recordings WHERE RecordingID = ?");
            $result = $stmt->execute([$recordingId]);
            
            if ($result && $stmt->rowCount() > 0) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Recording not found'];
            }
            
        } catch (Exception $e) {
            error_log("Error deleting recording: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $handler = new ExamRecordingHandler();
        $action = $_POST['action'];
        
        switch ($action) {
            case 'start_recording':
                if (!isset($_POST['attempt_id']) || !isset($_POST['candidate_id']) || !isset($_POST['exam_id'])) {
                    throw new Exception('Missing required parameters');
                }
                
                $result = $handler->startRecording(
                    $_POST['attempt_id'],
                    $_POST['candidate_id'],
                    $_POST['exam_id']
                );
                echo json_encode($result);
                break;
                
            case 'save_recording':
                if (!isset($_POST['recording_id']) || !isset($_FILES['recording_data'])) {
                    throw new Exception('Missing required parameters');
                }
                
                $recordingId = $_POST['recording_id'];
                $file = $_FILES['recording_data'];
                $fileName = $file['name'];
                $fileSize = $file['size'];
                $recordingData = file_get_contents($file['tmp_name']);
                $duration = $_POST['duration'] ?? 0;
                
                $result = $handler->saveRecordingData($recordingId, $recordingData, $fileName, $fileSize, $duration);
                echo json_encode($result);
                break;
                
            case 'stop_recording':
                if (!isset($_POST['recording_id'])) {
                    throw new Exception('Missing recording ID');
                }
                
                $result = $handler->stopRecording($_POST['recording_id']);
                echo json_encode($result);
                break;
                
            case 'get_recordings':
                if (!isset($_POST['attempt_id'])) {
                    throw new Exception('Missing attempt ID');
                }
                
                $result = $handler->getAttemptRecordings($_POST['attempt_id']);
                echo json_encode($result);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    exit;
}
?>
