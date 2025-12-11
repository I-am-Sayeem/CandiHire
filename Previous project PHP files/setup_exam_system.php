<?php
// setup_exam_system.php - Setup exam system with database tables and sample data
require_once 'Database.php';

echo "Setting up CandiHire Exam System...\n\n";

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception('Database connection not available');
    }
    
    // Read and execute the additional tables SQL
    $additionalTablesSQL = file_get_contents('create_exam_tables.sql');
    if ($additionalTablesSQL) {
        echo "Creating additional exam tables...\n";
        $pdo->exec($additionalTablesSQL);
        echo "✓ Additional tables created successfully\n\n";
    }
    
    // Populate question bank
    echo "Populating question bank...\n";
    include 'populate_question_bank.php';
    echo "✓ Question bank populated successfully\n\n";
    
    // Create some sample exams for testing
    echo "Creating sample exams...\n";
    
    // Get a company ID (assuming there's at least one company)
    $companyStmt = $pdo->query("SELECT CompanyID FROM Company_login_info LIMIT 1");
    $company = $companyStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($company) {
        $companyId = $company['CompanyID'];
        
        // Create sample manual exam
        $manualExamStmt = $pdo->prepare("
            INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, MaxAttempts, CreatedBy) 
            VALUES (?, 'Sample Manual Exam', 'manual', 'A sample manual exam for testing', 'Please answer all questions carefully.', 3600, 5, 70.00, 1, 'System')
        ");
        $manualExamStmt->execute([$companyId]);
        $manualExamId = $pdo->lastInsertId();
        
        // Add sample questions to manual exam
        $sampleQuestions = [
            [
                'text' => 'What is the primary purpose of version control systems?',
                'options' => [
                    ['text' => 'To compile code', 'correct' => false],
                    ['text' => 'To track changes in code over time', 'correct' => true],
                    ['text' => 'To debug applications', 'correct' => false],
                    ['text' => 'To deploy applications', 'correct' => false]
                ]
            ],
            [
                'text' => 'Which of the following is NOT a programming paradigm?',
                'options' => [
                    ['text' => 'Object-Oriented Programming', 'correct' => false],
                    ['text' => 'Functional Programming', 'correct' => false],
                    ['text' => 'Linear Programming', 'correct' => true],
                    ['text' => 'Procedural Programming', 'correct' => false]
                ]
            ],
            [
                'text' => 'What does API stand for?',
                'options' => [
                    ['text' => 'Application Programming Interface', 'correct' => true],
                    ['text' => 'Application Process Integration', 'correct' => false],
                    ['text' => 'Automated Programming Interface', 'correct' => false],
                    ['text' => 'Application Protocol Interface', 'correct' => false]
                ]
            ],
            [
                'text' => 'Which HTTP method is used to create a new resource?',
                'options' => [
                    ['text' => 'GET', 'correct' => false],
                    ['text' => 'POST', 'correct' => true],
                    ['text' => 'PUT', 'correct' => false],
                    ['text' => 'DELETE', 'correct' => false]
                ]
            ],
            [
                'text' => 'What is the time complexity of binary search?',
                'options' => [
                    ['text' => 'O(n)', 'correct' => false],
                    ['text' => 'O(log n)', 'correct' => true],
                    ['text' => 'O(n²)', 'correct' => false],
                    ['text' => 'O(1)', 'correct' => false]
                ]
            ]
        ];
        
        foreach ($sampleQuestions as $index => $question) {
            // Insert question
            $questionStmt = $pdo->prepare("
                INSERT INTO exam_questions (ExamID, QuestionType, QuestionText, QuestionOrder, Points, Difficulty, Category) 
                VALUES (?, 'multiple-choice', ?, ?, 1.00, 'medium', 'Programming')
            ");
            $questionStmt->execute([$manualExamId, $question['text'], $index + 1]);
            $questionId = $pdo->lastInsertId();
            
            // Insert options
            $optionStmt = $pdo->prepare("
                INSERT INTO exam_question_options (QuestionID, OptionText, IsCorrect, OptionOrder) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($question['options'] as $optionIndex => $option) {
                $optionStmt->execute([
                    $questionId,
                    $option['text'],
                    $option['correct'] ? 1 : 0,
                    $optionIndex + 1
                ]);
            }
        }
        
        echo "✓ Sample manual exam created\n";
        
        // Create sample auto-generated exam
        $autoExamStmt = $pdo->prepare("
            INSERT INTO exams (CompanyID, ExamTitle, ExamType, Description, Instructions, Duration, QuestionCount, PassingScore, MaxAttempts, CreatedBy) 
            VALUES (?, 'Sample Auto Exam', 'auto-generated', 'A sample auto-generated exam for testing', 'Please answer all questions carefully.', 1800, 10, 70.00, 1, 'System')
        ");
        $autoExamStmt->execute([$companyId]);
        $autoExamId = $pdo->lastInsertId();
        
        // Get 10 random questions from question bank
        $randomQuestionsStmt = $pdo->prepare("
            SELECT qb.QuestionID, qb.QuestionText, qb.Difficulty, qb.Category
            FROM question_bank qb
            WHERE qb.Department = 'Software Engineering'
            ORDER BY RAND()
            LIMIT 10
        ");
        $randomQuestionsStmt->execute();
        $randomQuestions = $randomQuestionsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($randomQuestions as $index => $question) {
            // Insert question
            $questionStmt = $pdo->prepare("
                INSERT INTO exam_questions (ExamID, QuestionType, QuestionText, QuestionOrder, Points, Difficulty, Category) 
                VALUES (?, 'multiple-choice', ?, ?, 1.00, ?, ?)
            ");
            $questionStmt->execute([
                $autoExamId,
                $question['QuestionText'],
                $index + 1,
                $question['Difficulty'],
                $question['Category']
            ]);
            $examQuestionId = $pdo->lastInsertId();
            
            // Get options from question bank
            $optionsStmt = $pdo->prepare("
                SELECT OptionText, IsCorrect, OptionOrder
                FROM question_bank_options
                WHERE QuestionID = ?
                ORDER BY OptionOrder
            ");
            $optionsStmt->execute([$question['QuestionID']]);
            $options = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Insert options
            $optionStmt = $pdo->prepare("
                INSERT INTO exam_question_options (QuestionID, OptionText, IsCorrect, OptionOrder) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($options as $option) {
                $optionStmt->execute([
                    $examQuestionId,
                    $option['OptionText'],
                    $option['IsCorrect'],
                    $option['OptionOrder']
                ]);
            }
        }
        
        echo "✓ Sample auto-generated exam created\n";
        
    } else {
        echo "⚠ No companies found. Please create a company account first.\n";
    }
    
    echo "\n🎉 Exam system setup completed successfully!\n\n";
    echo "Next steps:\n";
    echo "1. Create a company account and login\n";
    echo "2. Create exams using the CreateExam.php page\n";
    echo "3. Apply for jobs as a candidate to get assigned exams\n";
    echo "4. Take exams using the attendexam.php page\n\n";
    
} catch (Exception $e) {
    echo "❌ Error setting up exam system: " . $e->getMessage() . "\n";
    error_log("Exam system setup error: " . $e->getMessage());
}
?>
