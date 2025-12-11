# Retroactive Exam Assignment System

## Overview

This system solves the problem where candidates who applied for jobs before a company created an exam don't automatically get assigned that exam. The system ensures that when a company creates a new exam, it gets automatically assigned to all existing job applicants for that company.

## Problem Solved

**Before:** If a candidate applied for a job and the company later created an exam, the candidate wouldn't see the exam in their "Attend Exam" page.

**After:** When a company creates an exam, it automatically gets assigned to all existing job applicants, ensuring no one misses out on taking required assessments.

## How It Works

### 1. Automatic Assignment on Exam Creation

When a company creates an exam (either manually or automatically), the system:

1. **Creates the exam** in the database
2. **Automatically assigns it** to all existing job applicants for that company
3. **Sets a due date** (default: 7 days from creation)
4. **Logs the assignment** for tracking

### 2. Files Modified

#### `ManualExamCreation.php` & `AutoExamCreation.php`
- Added retroactive assignment after exam creation
- Uses the new `retroactive_exam_assignment.php` system

#### `attendexam.php`
- Added notification for newly assigned exams
- Shows toast message when candidate has new exams assigned

#### `CompanyDashboard.php`
- Added "Bulk Assignment" menu item for manual control

### 3. New Files Created

#### `retroactive_exam_assignment.php`
**Main system file containing:**

- `RetroactiveExamAssignment` class with methods:
  - `assignExamToExistingApplicants()` - Assigns exam to all company applicants
  - `assignExamToJobApplicants()` - Assigns exam to specific job applicants
  - `bulkAssignAllExams()` - Assigns all active exams to existing applicants
  - `getExamAssignmentStats()` - Gets statistics about exam assignments

- Standalone functions:
  - `assignExamToExistingApplicants()` - For easy integration
  - `assignExamToJobApplicants()` - For job-specific assignments

#### `bulk_exam_assignment.php`
**Admin interface for companies to:**

- View exam assignment statistics
- Manually assign individual exams to existing applicants
- Perform bulk assignment of all active exams
- Set custom due dates for assignments

#### `test_retroactive_assignment.php`
**Test script to verify functionality:**

- Tests statistics retrieval
- Tests individual exam assignment
- Tests bulk assignment
- Tests job-specific assignment
- Verifies assignments in database

## Usage

### For Companies

1. **Automatic Assignment:** When you create an exam, it's automatically assigned to existing applicants
2. **Manual Control:** Use "Bulk Assignment" from the dashboard to manually assign exams
3. **Statistics:** View assignment statistics and track completion rates

### For Candidates

1. **Automatic Notifications:** Get notified when new exams are assigned
2. **Seamless Experience:** All assigned exams appear in "Attend Exam" page
3. **No Missed Exams:** Never miss an exam due to timing issues

## Database Changes

The system uses existing tables:
- `exams` - Stores exam information
- `job_applications` - Links candidates to jobs
- `exam_assignments` - Stores exam assignments to candidates

No new tables were created, ensuring compatibility with existing data.

## Key Features

### 1. Smart Assignment
- Only assigns to applicants who don't already have the exam
- Prevents duplicate assignments
- Handles both company-wide and job-specific assignments

### 2. Flexible Due Dates
- Default: 7 days from assignment
- Configurable per assignment
- Automatic calculation based on exam creation date

### 3. Comprehensive Logging
- Logs all assignment activities
- Tracks success/failure of assignments
- Provides detailed error messages

### 4. Statistics & Monitoring
- Real-time assignment statistics
- Completion rate tracking
- Company-wide and per-exam metrics

### 5. Error Handling
- Graceful failure handling
- Transaction rollback on errors
- Detailed error logging

## Testing

Run the test script to verify functionality:

```bash
php test_retroactive_assignment.php
```

The test will:
- Check database connectivity
- Test statistics retrieval
- Test individual exam assignment
- Test bulk assignment
- Verify assignments in database

## Benefits

1. **No Missed Exams:** Candidates never miss exams due to timing
2. **Fair Assessment:** All applicants get equal opportunity to take exams
3. **Automatic Process:** No manual intervention required
4. **Flexible Control:** Companies can still manually manage assignments
5. **Better UX:** Candidates get notified of new assignments
6. **Comprehensive Tracking:** Full visibility into assignment status

## Future Enhancements

1. **Email Notifications:** Send email alerts for new exam assignments
2. **SMS Notifications:** Text message alerts for urgent exams
3. **Assignment Scheduling:** Schedule assignments for specific times
4. **Bulk Operations:** More advanced bulk assignment options
5. **Analytics Dashboard:** Detailed analytics and reporting

## Troubleshooting

### Common Issues

1. **No assignments created:** Check if there are existing applicants for the company
2. **Database errors:** Verify database connection and table structure
3. **Permission issues:** Ensure proper file permissions for PHP execution

### Debug Mode

Enable debug logging by checking the error logs for detailed information about assignment processes.

## Conclusion

The retroactive exam assignment system ensures that all job applicants have equal opportunity to take required assessments, regardless of when they applied relative to when the exam was created. This creates a fairer, more comprehensive assessment process while maintaining the flexibility and control that companies need.
