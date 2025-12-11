# Exam Question Assignment System

## Problem Solved

**Before:** When a company creates an exam without questions initially, and later adds questions to that exam, candidates who applied early don't see the exam in their "Attend Exam" page.

**After:** When a company adds questions to an existing exam, the system automatically assigns that exam ONLY to candidates who applied for jobs that have that specific exam. This ensures job-specific assignment rather than company-wide assignment.

## How It Works

### 1. Automatic Assignment When Questions Are Added

When a company adds questions to an exam (either manually or automatically), the system:

1. **Updates the question count** in the exam record
2. **Checks if the exam has questions** and is active
3. **Automatically assigns the exam** ONLY to candidates who applied for jobs that have that specific exam
4. **Sets a due date** (default: 7 days from assignment)
5. **Logs the assignment** for tracking

### 2. Files Created/Modified

#### New Files:
- `exam_question_assignment_handler.php` - Core system for handling question-based assignments
- `test_question_assignment.php` - Test script to verify functionality

#### Modified Files:
- `ManualExamCreation.php` - Added question assignment handling
- `AutoExamCreation.php` - Added question assignment handling  
- `bulk_exam_assignment.php` - Added "Check Missing" functionality
- `retroactive_exam_assignment.php` - Fixed column name issues

### 3. Key Functions

#### `handleExamQuestionAddition($examId, $companyId, $jobId = null)`
- Main function called when questions are added to an exam
- Updates question count and assigns exam to job-specific applicants

#### `checkAndAssignExamWithQuestions($examId, $companyId, $jobId = null)`
- Checks if an exam has questions and assigns it to job-specific applicants
- Only assigns if exam is active and has questions
- If jobId provided, only assigns to candidates who applied for that specific job

#### `checkAllExamsForMissingAssignments($companyId)`
- Bulk function to check all company exams for missing assignments
- Finds exams with questions that are missing assignments
- Only assigns to candidates who applied for jobs that have those exams

#### `updateExamQuestionCount($examId)`
- Updates the question count in the exam record
- Called whenever questions are added or removed

### 4. User Interface

#### For Companies:
- **Automatic Process:** When adding questions to exams, assignments are created automatically
- **Manual Control:** Use "Check Missing" button in bulk assignment page
- **Statistics:** View assignment statistics and track completion rates

#### For Candidates:
- **Automatic Notifications:** Get notified when new exams are assigned
- **Seamless Experience:** All assigned exams appear in "Attend Exam" page
- **No Missed Exams:** Never miss an exam due to timing issues

### 5. Test Results

The test script successfully demonstrates:

1. ✅ **Creates exam without questions** - Exam created with 0 questions
2. ✅ **Adds questions to exam** - Questions added successfully
3. ✅ **Handles question addition** - System processes the addition
4. ✅ **Creates assignments** - Exam assigned to existing applicants
5. ✅ **Verifies assignments** - Candidate can see the exam in their scheduled exams
6. ✅ **Bulk checking** - System can check for missing assignments

### 6. Database Changes

The system uses existing tables:
- `exams` - Stores exam information and question count
- `exam_questions` - Stores individual questions
- `job_applications` - Links candidates to jobs
- `exam_assignments` - Stores exam assignments to candidates

No new tables were created, ensuring compatibility with existing data.

### 7. Key Features

#### Smart Assignment
- Only assigns to applicants who don't already have the exam
- Prevents duplicate assignments
- Only assigns if exam has questions and is active

#### Flexible Due Dates
- Default: 7 days from assignment
- Configurable per assignment
- Automatic calculation based on assignment date

#### Comprehensive Logging
- Logs all assignment activities
- Tracks success/failure of assignments
- Provides detailed error messages

#### Error Handling
- Graceful failure handling
- Database transaction safety
- Detailed error logging

### 8. Usage Scenarios

#### Scenario 1: Job-Specific Exam Assignment (Your Exact Requirement)
1. **Company creates exam for specific job** (e.g., Frontend Developer) without questions initially
2. **Candidates apply for different jobs** (Frontend Developer, Backend Developer, etc.)
3. **Company later adds questions** to the Frontend Developer exam
4. **System assigns exam ONLY to Frontend Developer applicants** - Backend Developer applicants don't get it
5. **Only relevant candidates see the exam** in their "Attend Exam" page

#### Scenario 2: Company Creates Exam With Questions
1. Company creates exam with questions for specific job
2. System automatically assigns exam to applicants for that job only
3. Candidates see exam in their "Attend Exam" page

#### Scenario 3: Manual Check for Missing Assignments
1. Company uses "Check Missing" button in bulk assignment page
2. System finds exams with questions but missing assignments
3. System creates missing assignments for relevant job applicants only
4. Candidates see newly assigned exams

### 9. Job-Specific Behavior

The system now works exactly as you requested:

- **No Auto-Assignment**: If company doesn't create an exam for a job, no exam is assigned to candidates
- **Job-Specific Assignment**: When company adds questions to an exam, only candidates who applied for that specific job get the exam
- **Early Applicants Covered**: Candidates who applied early (before exam had questions) still get the exam when questions are added
- **No Cross-Job Assignment**: Candidates who applied for different jobs don't get exams meant for other jobs

### 10. Benefits

1. **No Missed Exams:** Candidates never miss exams due to timing
2. **Fair Assessment:** All applicants get equal opportunity
3. **Automatic Process:** No manual intervention required
4. **Flexible Control:** Companies can still manage manually
5. **Better UX:** Candidates get notified of new assignments
6. **Comprehensive Tracking:** Full visibility into assignment status

### 11. Testing

Run the test script to verify functionality:

```bash
php test_question_assignment.php
```

The test will:
- Create an exam without questions
- Add questions to the exam
- Verify assignments are created
- Check that candidates can see the exam
- Clean up test data

### 12. Troubleshooting

#### Common Issues

1. **No assignments created:** Check if there are existing applicants for the company
2. **Database errors:** Verify database connection and table structure
3. **Column errors:** Ensure exam_assignments table has correct column names

#### Debug Mode

Enable debug logging by checking the error logs for detailed information about assignment processes.

## Conclusion

The exam question assignment system now works exactly as you requested:

1. **No Auto-Assignment**: If a company doesn't create an exam for a job post, no exam is assigned to candidates
2. **Job-Specific Assignment**: When a company adds questions to an exam, only candidates who applied for that specific job get the exam assigned
3. **Early Applicant Coverage**: Candidates who applied early (before the exam had questions) will automatically get the exam in their scheduled exam cards when the company adds questions
4. **No Cross-Job Assignment**: Candidates who applied for different jobs don't get exams meant for other jobs

This creates a fair, job-specific assessment process that ensures candidates only get exams relevant to the positions they applied for, while still covering the scenario where early applicants need to be retroactively assigned exams when questions are added later.
