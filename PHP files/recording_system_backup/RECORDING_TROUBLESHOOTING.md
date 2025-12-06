# Recording System Troubleshooting Guide

## Common Issues and Solutions

### 1. "Failed to start recording. Please refresh the page and try again."

This is the most common error. Here are the steps to resolve it:

#### **Step 1: Check Browser Console**
1. Open browser developer tools (F12)
2. Go to the Console tab
3. Look for any JavaScript errors
4. Check for specific error messages

#### **Step 2: Verify Prerequisites**
- **HTTPS Required**: Screen recording requires HTTPS or localhost
- **Browser Support**: Modern browsers only (Chrome, Firefox, Edge)
- **Permissions**: Camera and microphone permissions must be granted

#### **Step 3: Test Step by Step**

1. **Test the setup script:**
   ```bash
   php setup_recording_system.php
   ```

2. **Test the recording handler:**
   ```bash
   php debug_recording_system.php
   ```

3. **Test the web interface:**
   - Navigate to `test_recording_page.php`
   - Click "Test Permissions" first
   - Then try "Start Recording"

### 2. Database Issues

#### **Error: "Table 'exam_recordings' already exists"**
This is normal if the table was already created. The system will continue to work.

#### **Error: "SQL syntax error"**
This was fixed in the latest version. Make sure you have the updated `exam_recording_handler.php`.

### 3. Permission Issues

#### **"Permission denied" Error**
- Ensure the user grants camera permission
- Check if the site is served over HTTPS
- Some browsers require user interaction before requesting permissions

#### **"Screen recording not available"**
- This is normal on HTTP connections
- Screen recording requires HTTPS
- The system will work with webcam recording only

### 4. Browser Compatibility Issues

#### **Check Browser Support**
Open browser console and run:
```javascript
// Check MediaRecorder support
console.log('MediaRecorder:', typeof MediaRecorder !== 'undefined');

// Check getUserMedia support
console.log('getUserMedia:', !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia));

// Check getDisplayMedia support
console.log('getDisplayMedia:', !!(navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia));
```

#### **Supported Browsers**
- ✅ Chrome 47+
- ✅ Firefox 25+
- ✅ Edge 79+
- ⚠️ Safari 14+ (limited support)

### 5. HTTPS Issues

#### **Screen Recording Requires HTTPS**
- Screen recording API only works on HTTPS or localhost
- Use a local development server with HTTPS
- Or use a service like ngrok for testing

#### **Quick HTTPS Setup for Testing**
```bash
# Using Python (if available)
python -m http.server 8000

# Using Node.js (if available)
npx http-server -S

# Using ngrok (recommended)
ngrok http 8000
```

### 6. Server Configuration Issues

#### **Check PHP Configuration**
- Ensure PDO MySQL extension is enabled
- Check file upload limits
- Verify database connection

#### **Check File Permissions**
- Ensure web server can write to uploads directory
- Check database user permissions

### 7. Debugging Steps

#### **Step 1: Enable Debug Mode**
Add this to the top of your PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

#### **Step 2: Check Database Connection**
```php
// Add this to test database connection
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, DB_OPTIONS);
    echo "Database connection successful";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
```

#### **Step 3: Test Recording Handler**
```php
// Test the recording handler directly
require_once 'exam_recording_handler.php';
$handler = new ExamRecordingHandler();
$result = $handler->startRecording(1, 1, 1);
var_dump($result);
```

### 8. Quick Fixes

#### **Fix 1: Clear Browser Cache**
- Clear browser cache and cookies
- Try in incognito/private mode
- Try a different browser

#### **Fix 2: Restart Services**
- Restart web server
- Restart database service
- Clear PHP opcache

#### **Fix 3: Check File Paths**
- Ensure all files are in the correct directory
- Check file permissions
- Verify include paths

### 9. Production Deployment

#### **For Production Use**
1. **Enable HTTPS**: Use SSL certificate
2. **Database Optimization**: Add proper indexes
3. **File Storage**: Consider using cloud storage for large files
4. **Monitoring**: Set up error logging and monitoring
5. **Security**: Implement proper access controls

#### **Performance Considerations**
- Monitor database size
- Implement file cleanup policies
- Consider video compression
- Set up CDN for video delivery

### 10. Getting Help

#### **Debug Information to Collect**
1. Browser console errors
2. Server error logs
3. Database connection status
4. PHP version and extensions
5. Browser version and OS

#### **Test Files Available**
- `setup_recording_system.php` - Initial setup
- `debug_recording_system.php` - Debug backend
- `test_recording_page.php` - Test frontend
- `test_recording_system.php` - Full system test

### 11. Common Error Messages

| Error Message | Cause | Solution |
|---------------|-------|----------|
| "Failed to start recording" | JavaScript error or permission issue | Check console, grant permissions |
| "Permission denied" | User denied camera access | Ask user to allow camera |
| "Screen recording not available" | Not on HTTPS | Use HTTPS or localhost |
| "Database connection failed" | Database configuration issue | Check database settings |
| "Table already exists" | Normal message | Ignore, system will work |
| "MediaRecorder not supported" | Old browser | Use modern browser |

### 12. Testing Checklist

- [ ] Database table created successfully
- [ ] PHP recording handler works
- [ ] Browser supports MediaRecorder API
- [ ] HTTPS enabled (for screen recording)
- [ ] Camera permissions granted
- [ ] No JavaScript errors in console
- [ ] Recording starts and stops properly
- [ ] Data is saved to database
- [ ] Admin can view recordings

### 13. Emergency Fallback

If recording system fails completely:
1. Disable recording in `take_exam.php`
2. Comment out the recording script include
3. Remove recording-related UI elements
4. System will work without recording

### 14. Contact Support

If you continue to have issues:
1. Run all test scripts
2. Collect debug information
3. Check this troubleshooting guide
4. Contact the development team with specific error messages
