<?php
// view_exam_recordings.php - Admin interface to view exam recordings
require_once 'Database.php';
require_once 'session_manager.php';
require_once 'exam_recording_handler.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: admin_login_handler.php');
    exit;
}

$handler = null;
$recordings = [];
$error = null;

// Initialize handler
try {
    $handler = new ExamRecordingHandler();
} catch (Exception $e) {
    $error = "Error initializing recording handler: " . $e->getMessage();
}

// Get all recordings
try {
    if ($handler) {
        $stmt = $pdo->prepare("
        SELECT 
            er.RecordingID,
            er.AttemptID,
            er.CandidateID,
            er.ExamID,
            er.RecordingType,
            er.FileName,
            er.FileSize,
            er.Duration,
            er.StartTime,
            er.EndTime,
            er.Status,
            cli.FullName as CandidateName,
            e.ExamTitle,
            jp.JobTitle
        FROM exam_recordings er
        JOIN candidate_login_info cli ON er.CandidateID = cli.CandidateID
        JOIN exams e ON er.ExamID = e.ExamID
        LEFT JOIN exam_assignments ea ON er.AttemptID = ea.AssignmentID
        LEFT JOIN job_postings jp ON ea.JobID = jp.JobID
        ORDER BY er.StartTime DESC
    ");
        $stmt->execute();
        $recordings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error = "Error loading recordings: " . $e->getMessage();
}

// Handle viewing specific recording
if (isset($_GET['view']) && is_numeric($_GET['view']) && $handler) {
    $recordingId = $_GET['view'];
    $recordingResult = $handler->getRecordingData($recordingId);
    
    if ($recordingResult['success']) {
        $recording = $recordingResult['data'];
        header('Content-Type: ' . $recording['MimeType']);
        header('Content-Disposition: inline; filename="' . $recording['FileName'] . '"');
        echo $recording['RecordingData'];
        exit;
    } else {
        $error = "Error loading recording: " . $recordingResult['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Recordings - CandiHire Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #0d1117;
            --bg-secondary: #161b22;
            --bg-tertiary: #21262d;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
            --accent: #58a6ff;
            --accent-hover: #79c0ff;
            --border: #30363d;
            --success: #3fb950;
            --danger: #f85149;
            --warning: #d29922;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans', Helvetica, Arial, sans-serif;
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }

        .header h1 {
            color: var(--accent);
            margin-bottom: 10px;
        }

        .header p {
            color: var(--text-secondary);
        }

        .recordings-table {
            background: var(--bg-secondary);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .table-header {
            background: var(--bg-tertiary);
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .table-header h2 {
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .table-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            color: var(--text-primary);
        }

        .recording-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .recording-type.screen {
            background: rgba(88, 166, 255, 0.2);
            color: var(--accent);
        }

        .recording-type.webcam {
            background: rgba(63, 185, 80, 0.2);
            color: var(--success);
        }

        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status.recording {
            background: rgba(248, 81, 73, 0.2);
            color: var(--danger);
        }

        .status.completed {
            background: rgba(63, 185, 80, 0.2);
            color: var(--success);
        }

        .status.failed {
            background: rgba(248, 81, 73, 0.2);
            color: var(--danger);
        }

        .action-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .action-btn:hover {
            background: var(--accent-hover);
        }

        .action-btn.danger {
            background: var(--danger);
        }

        .action-btn.danger:hover {
            background: #e74c3c;
        }

        .file-size {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .duration {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .no-recordings {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .no-recordings i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .error-message {
            background: var(--danger);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            table {
                font-size: 0.8rem;
            }
            
            th, td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-video"></i> Exam Recordings</h1>
            <p>View and manage screen recordings and webcam recordings from exam attempts</p>
        </div>

        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="recordings-table">
            <div class="table-header">
                <h2>All Recordings</h2>
                <p>Total recordings: <?php echo count($recordings); ?></p>
            </div>

            <?php if (empty($recordings)): ?>
            <div class="no-recordings">
                <i class="fas fa-video-slash"></i>
                <h3>No Recordings Found</h3>
                <p>No exam recordings have been captured yet.</p>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Recording ID</th>
                        <th>Type</th>
                        <th>Candidate</th>
                        <th>Exam</th>
                        <th>Job Position</th>
                        <th>File Size</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Start Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recordings as $recording): ?>
                    <tr>
                        <td><?php echo $recording['RecordingID']; ?></td>
                        <td>
                            <span class="recording-type <?php echo $recording['RecordingType']; ?>">
                                <?php echo ucfirst($recording['RecordingType']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($recording['CandidateName']); ?></td>
                        <td><?php echo htmlspecialchars($recording['ExamTitle']); ?></td>
                        <td><?php echo htmlspecialchars($recording['JobTitle'] ?? 'N/A'); ?></td>
                        <td class="file-size">
                            <?php echo $recording['FileSize'] > 0 ? formatFileSize($recording['FileSize']) : 'N/A'; ?>
                        </td>
                        <td class="duration">
                            <?php echo $recording['Duration'] > 0 ? formatDuration($recording['Duration']) : 'N/A'; ?>
                        </td>
                        <td>
                            <span class="status <?php echo $recording['Status']; ?>">
                                <?php echo ucfirst($recording['Status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y H:i', strtotime($recording['StartTime'])); ?></td>
                        <td>
                            <?php if ($recording['FileSize'] > 0): ?>
                            <a href="?view=<?php echo $recording['RecordingID']; ?>" 
                               class="action-btn" target="_blank">
                                <i class="fas fa-play"></i> View
                            </a>
                            <?php else: ?>
                            <span style="color: var(--text-secondary); font-size: 0.8rem;">No data</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
    } else {
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
?>
