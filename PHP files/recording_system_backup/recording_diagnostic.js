/**
 * Recording System Diagnostic Script
 * Run this in the browser console to diagnose recording issues
 */

console.log('=== RECORDING SYSTEM DIAGNOSTIC ===');

// 1. Check basic browser support
console.log('\n1. BROWSER COMPATIBILITY:');
console.log('User Agent:', navigator.userAgent);
console.log('Protocol:', location.protocol);
console.log('Hostname:', location.hostname);

// 2. Check MediaRecorder API
console.log('\n2. MEDIARECORDER API:');
if (typeof MediaRecorder !== 'undefined') {
    console.log('✓ MediaRecorder API is available');
    console.log('Supported MIME types:', MediaRecorder.isTypeSupported('video/webm;codecs=vp8') ? 'video/webm' : 'Not supported');
} else {
    console.log('✗ MediaRecorder API is NOT available');
}

// 3. Check MediaDevices API
console.log('\n3. MEDIADEVICES API:');
if (navigator.mediaDevices) {
    console.log('✓ MediaDevices API is available');
    
    if (navigator.mediaDevices.getUserMedia) {
        console.log('✓ getUserMedia is available');
    } else {
        console.log('✗ getUserMedia is NOT available');
    }
    
    if (navigator.mediaDevices.getDisplayMedia) {
        console.log('✓ getDisplayMedia is available');
    } else {
        console.log('✗ getDisplayMedia is NOT available');
    }
} else {
    console.log('✗ MediaDevices API is NOT available');
}

// 4. Check for required elements
console.log('\n4. DOM ELEMENTS:');
const attemptId = document.querySelector('[data-attempt-id]')?.dataset.attemptId;
const candidateId = document.querySelector('[data-candidate-id]')?.dataset.candidateId;
const examId = document.querySelector('[data-exam-id]')?.dataset.examId;

console.log('Attempt ID:', attemptId || 'NOT FOUND');
console.log('Candidate ID:', candidateId || 'NOT FOUND');
console.log('Exam ID:', examId || 'NOT FOUND');

// 5. Check for recording script
console.log('\n5. RECORDING SCRIPT:');
if (typeof ExamRecordingSystem !== 'undefined') {
    console.log('✓ ExamRecordingSystem class is loaded');
} else {
    console.log('✗ ExamRecordingSystem class is NOT loaded');
}

// 6. Test permission request
console.log('\n6. PERMISSION TEST:');
async function testPermissions() {
    try {
        console.log('Requesting camera permission...');
        const stream = await navigator.mediaDevices.getUserMedia({
            video: true,
            audio: true
        });
        console.log('✓ Camera permission granted');
        console.log('Video tracks:', stream.getVideoTracks().length);
        console.log('Audio tracks:', stream.getAudioTracks().length);
        
        // Stop the stream
        stream.getTracks().forEach(track => track.stop());
        
        return true;
    } catch (error) {
        console.log('✗ Camera permission denied:', error.message);
        return false;
    }
}

// 7. Test screen capture (if HTTPS)
console.log('\n7. SCREEN CAPTURE TEST:');
async function testScreenCapture() {
    if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
        console.log('⚠ Screen capture requires HTTPS (skipping test)');
        return false;
    }
    
    try {
        console.log('Requesting screen capture permission...');
        const stream = await navigator.mediaDevices.getDisplayMedia({
            video: true,
            audio: false
        });
        console.log('✓ Screen capture permission granted');
        console.log('Video tracks:', stream.getVideoTracks().length);
        
        // Stop the stream
        stream.getTracks().forEach(track => track.stop());
        
        return true;
    } catch (error) {
        console.log('✗ Screen capture permission denied:', error.message);
        return false;
    }
}

// 8. Test recording handler API
console.log('\n8. RECORDING HANDLER API:');
async function testRecordingAPI() {
    try {
        const formData = new FormData();
        formData.append('action', 'start_recording');
        formData.append('attempt_id', attemptId || '1');
        formData.append('candidate_id', candidateId || '1');
        formData.append('exam_id', examId || '1');
        
        const response = await fetch('exam_recording_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        console.log('✓ Recording API response:', result);
        return result.success;
    } catch (error) {
        console.log('✗ Recording API error:', error.message);
        return false;
    }
}

// Run all tests
async function runDiagnostic() {
    console.log('\n=== RUNNING TESTS ===');
    
    const permissionTest = await testPermissions();
    const screenTest = await testScreenCapture();
    const apiTest = await testRecordingAPI();
    
    console.log('\n=== DIAGNOSTIC SUMMARY ===');
    console.log('Camera Permission:', permissionTest ? '✓' : '✗');
    console.log('Screen Capture:', screenTest ? '✓' : '✗');
    console.log('Recording API:', apiTest ? '✓' : '✗');
    
    if (permissionTest && apiTest) {
        console.log('\n🎉 RECORDING SYSTEM SHOULD WORK!');
        console.log('The issue might be in the initialization or error handling.');
    } else {
        console.log('\n❌ RECORDING SYSTEM HAS ISSUES');
        console.log('Fix the failed tests above before proceeding.');
    }
}

// Auto-run diagnostic
runDiagnostic();

// Export functions for manual testing
window.testPermissions = testPermissions;
window.testScreenCapture = testScreenCapture;
window.testRecordingAPI = testRecordingAPI;
window.runDiagnostic = runDiagnostic;

console.log('\n=== MANUAL TESTING ===');
console.log('You can run these functions manually:');
console.log('- testPermissions()');
console.log('- testScreenCapture()');
console.log('- testRecordingAPI()');
console.log('- runDiagnostic()');
