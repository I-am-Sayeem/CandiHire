/**
 * Exam Recording System
 * Handles screen recording and webcam recording during exams
 */
class ExamRecordingSystem {
    constructor(attemptId, candidateId, examId) {
        this.attemptId = attemptId;
        this.candidateId = candidateId;
        this.examId = examId;
        this.screenRecordingId = null;
        this.webcamRecordingId = null;
        this.screenRecorder = null;
        this.webcamRecorder = null;
        this.screenStream = null;
        this.webcamStream = null;
        this.isRecording = false;
        this.recordingStartTime = null;
        
        this.init();
    }
    
    async init() {
        try {
            // Request permissions and start recording
            await this.requestPermissions();
            await this.startRecording();
        } catch (error) {
            console.error('Failed to initialize recording system:', error);
            this.showError('Failed to start recording. Please refresh the page and try again.');
        }
    }
    
    async requestPermissions() {
        try {
            // Check if we're on HTTPS
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                console.warn('Screen recording requires HTTPS. Proceeding with webcam only.');
                this.showWarning('Screen recording requires HTTPS. Only webcam recording will be available.');
            }
            
            // Request webcam permission first (works on HTTP)
            this.webcamStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    frameRate: { ideal: 15 }
                },
                audio: true
            });
            
            // Set up event listener for webcam stream end
            this.webcamStream.getVideoTracks()[0].addEventListener('ended', () => {
                this.handleWebcamEnd();
            });
            
            // Try to request screen capture permission (requires HTTPS)
            if (location.protocol === 'https:' || location.hostname === 'localhost') {
                try {
                    this.screenStream = await navigator.mediaDevices.getDisplayMedia({
                        video: {
                            mediaSource: 'screen',
                            width: { ideal: 1920 },
                            height: { ideal: 1080 },
                            frameRate: { ideal: 15 }
                        },
                        audio: false // We'll capture audio separately from webcam
                    });
                    
                    // Set up event listener for screen stream end
                    this.screenStream.getVideoTracks()[0].addEventListener('ended', () => {
                        this.handleScreenCaptureEnd();
                    });
                } catch (screenError) {
                    console.warn('Screen recording not available:', screenError);
                    this.showWarning('Screen recording is not available. Only webcam recording will be used.');
                }
            }
            
        } catch (error) {
            console.error('Permission request failed:', error);
            throw new Error('Camera permission is required for this exam.');
        }
    }
    
    async startRecording() {
        try {
            // Get recording IDs from server
            const response = await this.callRecordingAPI('start_recording', {
                attempt_id: this.attemptId,
                candidate_id: this.candidateId,
                exam_id: this.examId
            });
            
            if (!response.success) {
                throw new Error(response.error || 'Failed to start recording session');
            }
            
            this.screenRecordingId = response.screen_recording_id;
            this.webcamRecordingId = response.webcam_recording_id;
            
            // Start screen recording
            await this.startScreenRecording();
            
            // Start webcam recording
            await this.startWebcamRecording();
            
            this.isRecording = true;
            this.recordingStartTime = Date.now();
            
            this.showRecordingStatus(true);
            console.log('Recording started successfully');
            
        } catch (error) {
            console.error('Failed to start recording:', error);
            throw error;
        }
    }
    
    async startScreenRecording() {
        try {
            if (!this.screenStream) {
                console.log('Screen recording not available, skipping...');
                return;
            }
            
            const options = {
                mimeType: 'video/webm;codecs=vp8',
                videoBitsPerSecond: 1000000 // 1 Mbps
            };
            
            this.screenRecorder = new MediaRecorder(this.screenStream, options);
            
            this.screenRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    this.saveRecordingData(this.screenRecordingId, event.data, 'screen');
                }
            };
            
            this.screenRecorder.onstop = () => {
                console.log('Screen recording stopped');
            };
            
            this.screenRecorder.start(10000); // Record in 10-second chunks
            
        } catch (error) {
            console.error('Failed to start screen recording:', error);
            // Don't throw error, just log it
            console.log('Continuing without screen recording...');
        }
    }
    
    async startWebcamRecording() {
        try {
            const options = {
                mimeType: 'video/webm;codecs=vp8',
                videoBitsPerSecond: 500000 // 500 Kbps
            };
            
            this.webcamRecorder = new MediaRecorder(this.webcamStream, options);
            
            this.webcamRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    this.saveRecordingData(this.webcamRecordingId, event.data, 'webcam');
                }
            };
            
            this.webcamRecorder.onstop = () => {
                console.log('Webcam recording stopped');
            };
            
            this.webcamRecorder.start(10000); // Record in 10-second chunks
            
        } catch (error) {
            console.error('Failed to start webcam recording:', error);
            throw error;
        }
    }
    
    async saveRecordingData(recordingId, data, type) {
        try {
            const formData = new FormData();
            formData.append('action', 'save_recording');
            formData.append('recording_id', recordingId);
            formData.append('recording_data', data, `${type}_chunk_${Date.now()}.webm`);
            formData.append('duration', Math.floor((Date.now() - this.recordingStartTime) / 1000));
            
            const response = await fetch('exam_recording_handler.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (!result.success) {
                console.error(`Failed to save ${type} recording:`, result.error);
            }
            
        } catch (error) {
            console.error(`Error saving ${type} recording:`, error);
        }
    }
    
    async stopRecording() {
        try {
            if (!this.isRecording) return;
            
            this.isRecording = false;
            
            // Stop screen recording
            if (this.screenRecorder && this.screenRecorder.state === 'recording') {
                this.screenRecorder.stop();
            }
            
            // Stop webcam recording
            if (this.webcamRecorder && this.webcamRecorder.state === 'recording') {
                this.webcamRecorder.stop();
            }
            
            // Stop streams
            if (this.screenStream) {
                this.screenStream.getTracks().forEach(track => track.stop());
            }
            
            if (this.webcamStream) {
                this.webcamStream.getTracks().forEach(track => track.stop());
            }
            
            // Notify server that recording has stopped
            if (this.screenRecordingId) {
                try {
                    await this.callRecordingAPI('stop_recording', {
                        recording_id: this.screenRecordingId
                    });
                } catch (error) {
                    console.warn('Failed to stop screen recording on server:', error);
                }
            }
            
            if (this.webcamRecordingId) {
                try {
                    await this.callRecordingAPI('stop_recording', {
                        recording_id: this.webcamRecordingId
                    });
                } catch (error) {
                    console.warn('Failed to stop webcam recording on server:', error);
                }
            }
            
            this.showRecordingStatus(false);
            console.log('Recording stopped successfully');
            
        } catch (error) {
            console.error('Error stopping recording:', error);
        }
    }
    
    handleScreenCaptureEnd() {
        console.log('Screen capture ended by user');
        this.showWarning('Screen recording has been stopped. This may affect your exam validity.');
    }
    
    handleWebcamEnd() {
        console.log('Webcam ended by user');
        this.showWarning('Webcam recording has been stopped. This may affect your exam validity.');
    }
    
    async callRecordingAPI(action, data) {
        const formData = new FormData();
        formData.append('action', action);
        
        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }
        
        const response = await fetch('exam_recording_handler.php', {
            method: 'POST',
            body: formData
        });
        
        return await response.json();
    }
    
    showRecordingStatus(isRecording) {
        const statusElement = document.getElementById('recording-status');
        if (statusElement) {
            statusElement.innerHTML = isRecording ? 
                '<i class="fas fa-video" style="color: #f85149;"></i> Recording in progress...' :
                '<i class="fas fa-video-slash" style="color: #8b949e;"></i> Recording stopped';
        }
    }
    
    showError(message) {
        const errorElement = document.getElementById('recording-error');
        if (errorElement) {
            errorElement.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
            errorElement.style.display = 'block';
        }
    }
    
    showWarning(message) {
        const warningElement = document.getElementById('recording-warning');
        if (warningElement) {
            warningElement.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
            warningElement.style.display = 'block';
        }
    }
}

// Initialize recording system when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== EXAM RECORDING SYSTEM INITIALIZATION ===');
    
    // Get exam parameters from URL or data attributes
    const urlParams = new URLSearchParams(window.location.search);
    const attemptId = urlParams.get('attempt_id') || document.querySelector('[data-attempt-id]')?.dataset.attemptId;
    const candidateId = urlParams.get('candidate_id') || document.querySelector('[data-candidate-id]')?.dataset.candidateId;
    const examId = urlParams.get('exam_id') || document.querySelector('[data-exam-id]')?.dataset.examId;
    
    console.log('Attempt ID:', attemptId);
    console.log('Candidate ID:', candidateId);
    console.log('Exam ID:', examId);
    
    // Check if all required parameters are available
    if (!attemptId || !candidateId || !examId) {
        console.error('Missing required parameters for recording system');
        console.error('Attempt ID:', attemptId);
        console.error('Candidate ID:', candidateId);
        console.error('Exam ID:', examId);
        
        // Show error message to user
        const errorElement = document.getElementById('recording-error');
        if (errorElement) {
            errorElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Recording system initialization failed. Missing required parameters.';
            errorElement.style.display = 'block';
        }
        return;
    }
    
    // Check if we're on HTTPS or localhost
    if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
        console.warn('Not on HTTPS - screen recording will not be available');
    }
    
    // Check browser compatibility
    if (typeof MediaRecorder === 'undefined') {
        console.error('MediaRecorder API not supported');
        const errorElement = document.getElementById('recording-error');
        if (errorElement) {
            errorElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Your browser does not support recording. Please use a modern browser.';
            errorElement.style.display = 'block';
        }
        return;
    }
    
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        console.error('getUserMedia API not supported');
        const errorElement = document.getElementById('recording-error');
        if (errorElement) {
            errorElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Camera access not supported. Please use a modern browser.';
            errorElement.style.display = 'block';
        }
        return;
    }
    
    try {
        console.log('Initializing recording system...');
        window.examRecordingSystem = new ExamRecordingSystem(attemptId, candidateId, examId);
        console.log('Recording system initialized successfully');
        
        // Stop recording when exam is submitted
        document.getElementById('examForm')?.addEventListener('submit', function() {
            console.log('Exam form submitted - stopping recording');
            if (window.examRecordingSystem) {
                window.examRecordingSystem.stopRecording();
            }
        });
        
        // Stop recording when page is unloaded
        window.addEventListener('beforeunload', function() {
            console.log('Page unloading - stopping recording');
            if (window.examRecordingSystem && window.examRecordingSystem.isRecording) {
                window.examRecordingSystem.stopRecording();
            }
        });
        
    } catch (error) {
        console.error('Failed to initialize recording system:', error);
        const errorElement = document.getElementById('recording-error');
        if (errorElement) {
            errorElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Failed to initialize recording system: ' + error.message;
            errorElement.style.display = 'block';
        }
    }
});
