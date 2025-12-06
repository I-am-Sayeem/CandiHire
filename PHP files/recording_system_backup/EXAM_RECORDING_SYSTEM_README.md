# Exam Recording System

This system implements screen recording and webcam recording functionality for the CandiHire exam system. It captures both screen activity and webcam feed during exam sessions and stores them in the database for security and monitoring purposes.

## Features

- **Screen Recording**: Captures the candidate's screen during the exam
- **Webcam Recording**: Records the candidate's webcam feed for identity verification
- **Real-time Recording**: Continuous recording throughout the exam session
- **Database Storage**: All recordings are stored securely in the database
- **Admin Interface**: View and manage recordings through an admin panel
- **Permission Handling**: Proper browser permission requests for recording
- **Error Handling**: Comprehensive error handling and user feedback

## Files Created/Modified

### New Files
1. `create_exam_recordings_table.sql` - Database schema for storing recordings
2. `exam_recording_handler.php` - Backend handler for recording operations
3. `exam_recording.js` - Frontend JavaScript for recording functionality
4. `view_exam_recordings.php` - Admin interface to view recordings
5. `test_recording_system.php` - Test script for the recording system

### Modified Files
1. `take_exam.php` - Updated to include recording functionality

## Installation

### 1. Database Setup
Run the SQL script to create the recordings table:
```sql
-- Execute the contents of create_exam_recordings_table.sql
-- This creates the exam_recordings table with proper indexes
```

### 2. File Permissions
Ensure the web server has write permissions to the uploads directory (if used for temporary files).

### 3. HTTPS Requirement
The recording system requires HTTPS for screen capture functionality. Ensure your server is configured with SSL.

## Usage

### For Candidates
1. When starting an exam, the system will automatically request permissions for:
   - Screen recording (for screen capture)
   - Camera access (for webcam recording)
2. A recording status indicator will show the current recording state
3. Recordings are automatically saved in chunks during the exam
4. When the exam is submitted, recordings are finalized and stored

### For Administrators
1. Access the admin panel at `view_exam_recordings.php`
2. View all recordings with details including:
   - Recording type (screen/webcam)
   - Candidate information
   - Exam details
   - File size and duration
   - Recording status
3. Click "View" to play recordings directly in the browser

## Technical Details

### Recording Process
1. **Initialization**: When the exam page loads, the system requests permissions
2. **Stream Setup**: Creates MediaRecorder instances for both screen and webcam
3. **Chunked Recording**: Records in 10-second chunks to prevent data loss
4. **Database Storage**: Each chunk is immediately saved to the database
5. **Finalization**: When exam ends, recordings are marked as completed

### Database Schema
```sql
CREATE TABLE exam_recordings (
    RecordingID INT AUTO_INCREMENT PRIMARY KEY,
    AttemptID INT NOT NULL,
    CandidateID INT NOT NULL,
    ExamID INT NOT NULL,
    RecordingType ENUM('screen', 'webcam', 'combined') NOT NULL,
    RecordingData LONGBLOB NOT NULL,
    FileName VARCHAR(255) NOT NULL,
    FileSize INT NOT NULL,
    MimeType VARCHAR(100) NOT NULL,
    Duration INT NOT NULL,
    StartTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    EndTime TIMESTAMP NULL,
    Status ENUM('recording', 'completed', 'failed', 'processing') DEFAULT 'recording',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Browser Compatibility
- **Chrome**: Full support
- **Firefox**: Full support
- **Safari**: Limited support (may require additional configuration)
- **Edge**: Full support

### Security Considerations
1. **Data Encryption**: Consider encrypting recording data before storage
2. **Access Control**: Implement proper access controls for viewing recordings
3. **Data Retention**: Set up policies for automatic deletion of old recordings
4. **Privacy Compliance**: Ensure compliance with local privacy laws (GDPR, etc.)

## Configuration

### Recording Quality Settings
In `exam_recording.js`, you can adjust recording quality:

```javascript
// Screen recording options
const screenOptions = {
    mimeType: 'video/webm;codecs=vp8',
    videoBitsPerSecond: 1000000 // 1 Mbps
};

// Webcam recording options
const webcamOptions = {
    mimeType: 'video/webm;codecs=vp8',
    videoBitsPerSecond: 500000 // 500 Kbps
};
```

### Chunk Size
Adjust the recording chunk size in `exam_recording.js`:
```javascript
this.screenRecorder.start(10000); // 10-second chunks
```

## Troubleshooting

### Common Issues

1. **Permission Denied**
   - Ensure the site is served over HTTPS
   - Check browser permissions for camera and screen recording
   - Some browsers require user interaction before requesting permissions

2. **Recording Not Starting**
   - Check browser console for JavaScript errors
   - Verify database connection
   - Ensure exam attempt ID is properly generated

3. **Large File Sizes**
   - Adjust video bitrate settings
   - Consider implementing compression
   - Set up automatic cleanup of old recordings

4. **Playback Issues**
   - Ensure proper MIME type headers
   - Check browser support for WebM format
   - Verify recording data integrity

### Debug Mode
Enable debug logging by opening browser console and checking for error messages.

## Testing

Run the test script to verify the system:
```bash
php test_recording_system.php
```

This will:
- Test database connection
- Create the recordings table
- Test recording handler functionality
- Verify data storage and retrieval

## Performance Considerations

1. **Database Size**: Recordings can consume significant storage space
2. **Network Bandwidth**: Consider bandwidth limitations for chunk uploads
3. **Server Resources**: Monitor server CPU and memory usage during recording
4. **Concurrent Users**: Test system performance with multiple simultaneous recordings

## Future Enhancements

1. **Video Compression**: Implement server-side video compression
2. **Cloud Storage**: Integrate with cloud storage services
3. **Analytics**: Add recording analytics and monitoring
4. **Mobile Support**: Optimize for mobile devices
5. **Advanced Security**: Implement additional anti-cheating measures

## Support

For technical support or questions about the recording system, please refer to the system documentation or contact the development team.

## License

This recording system is part of the CandiHire project and follows the same licensing terms.
