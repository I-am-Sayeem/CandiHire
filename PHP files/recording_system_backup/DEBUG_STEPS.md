# Step-by-Step Debugging Guide

## 🔍 **Follow these steps to identify and fix the "Failed to start recording" error:**

### **Step 1: Check Browser Console**
1. Open your exam page (`take_exam.php`)
2. Press **F12** to open Developer Tools
3. Go to the **Console** tab
4. Look for any error messages
5. You should see messages like:
   ```
   === EXAM RECORDING SYSTEM INITIALIZATION ===
   Attempt ID: [some number]
   Candidate ID: [some number]
   Exam ID: [some number]
   ```

### **Step 2: Check for Missing Parameters**
If you see "Missing required parameters" in the console:
- The attempt ID, candidate ID, or exam ID is null/empty
- This means the exam attempt wasn't created properly

**Fix:** Check if the exam attempt creation is working by running:
```bash
php debug_recording_system.php
```

### **Step 3: Test Basic Recording**
1. Open `simple_recording_test.html` in your browser
2. Click "Test Camera Permission"
3. If this fails, the issue is with browser permissions or compatibility

### **Step 4: Test Browser Compatibility**
1. Open browser console on any page
2. Copy and paste this code:
```javascript
// Check MediaRecorder support
console.log('MediaRecorder:', typeof MediaRecorder !== 'undefined');

// Check getUserMedia support
console.log('getUserMedia:', !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia));

// Check getDisplayMedia support
console.log('getDisplayMedia:', !!(navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia));
```

### **Step 5: Test Recording API**
1. Open browser console on the exam page
2. Copy and paste this code:
```javascript
// Test the recording API
async function testAPI() {
    try {
        const formData = new FormData();
        formData.append('action', 'start_recording');
        formData.append('attempt_id', '1');
        formData.append('candidate_id', '1');
        formData.append('exam_id', '1');
        
        const response = await fetch('exam_recording_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        console.log('API Response:', result);
        return result.success;
    } catch (error) {
        console.log('API Error:', error);
        return false;
    }
}

testAPI();
```

### **Step 6: Common Issues and Solutions**

#### **Issue 1: "Missing required parameters"**
**Cause:** Attempt ID is null
**Solution:** 
1. Check if exam attempt creation is working
2. Verify database connection
3. Check if the exam exists

#### **Issue 2: "Permission denied"**
**Cause:** User denied camera access
**Solution:**
1. Click the camera icon in browser address bar
2. Allow camera access
3. Refresh the page

#### **Issue 3: "MediaRecorder not supported"**
**Cause:** Old browser or unsupported browser
**Solution:**
1. Use Chrome, Firefox, or Edge
2. Update your browser
3. Check if JavaScript is enabled

#### **Issue 4: "Screen recording not available"**
**Cause:** Not on HTTPS
**Solution:**
1. Use HTTPS or localhost
2. The system will work with webcam only

### **Step 7: Quick Fixes**

#### **Fix 1: Force Attempt ID**
If attempt ID is null, add this to `take_exam.php` before the form:
```php
<?php if (!$attemptId): ?>
<script>
console.error('Attempt ID is null - creating fallback');
// Create a temporary attempt ID for testing
const tempAttemptId = Date.now();
document.querySelector('[data-attempt-id]').dataset.attemptId = tempAttemptId;
</script>
<?php endif; ?>
```

#### **Fix 2: Disable Recording Temporarily**
If you want to disable recording temporarily, comment out this line in `take_exam.php`:
```php
<!-- <script src="exam_recording.js"></script> -->
```

#### **Fix 3: Test with Manual Parameters**
Add this to the exam page to test with manual parameters:
```javascript
// Override parameters for testing
window.testRecording = function() {
    const testSystem = new ExamRecordingSystem(1, 1, 1);
    return testSystem;
};
```

### **Step 8: Verify the Fix**

After applying fixes:
1. Refresh the exam page
2. Check browser console for success messages
3. Look for the recording status indicator
4. Try granting camera permissions

### **Step 9: Final Test**

1. Run the diagnostic script:
   ```bash
   php debug_recording_system.php
   ```

2. Open `simple_recording_test.html` and test all functions

3. Check the exam page and verify recording starts

### **Step 10: Get Help**

If you're still having issues, provide this information:
1. Browser console error messages
2. Results from `debug_recording_system.php`
3. Results from `simple_recording_test.html`
4. Your browser version and OS

## 🚀 **Quick Start (If Everything Works)**

1. **Run setup:** `php setup_recording_system.php`
2. **Test backend:** `php debug_recording_system.php`
3. **Test frontend:** Open `simple_recording_test.html`
4. **Test exam page:** Navigate to `take_exam.php`

The recording system should now work properly!
