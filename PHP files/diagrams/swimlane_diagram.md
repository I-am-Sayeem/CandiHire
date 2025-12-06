# Swimlane Diagram - CandiHire System

## Overview
Swimlane diagrams show the interaction between different actors (swimlanes) in the CandiHire system processes.

## 1. Job Application Process Swimlane

```mermaid
sequenceDiagram
    participant C as Candidate
    participant S as System
    participant DB as Database
    participant E as Email Service
    participant Co as Company
    
    Note over C,Co: Job Application Process
    
    C->>S: Search for jobs
    S->>DB: Query job database
    DB-->>S: Return job listings
    S-->>C: Display job results
    
    C->>S: Select job and apply
    S->>DB: Check job requirements
    DB-->>S: Return requirements
    
    alt Job requires exam
        S-->>C: Redirect to exam
        C->>S: Take required exam
        S->>DB: Store exam results
        S->>DB: Check if passed
        DB-->>S: Exam status
    end
    
    alt Exam passed or no exam required
        S->>DB: Create application record
        S->>E: Send application notification
        E-->>Co: Email notification
        S-->>C: Application confirmation
        Co->>S: Review application
        S->>DB: Update application status
        S->>E: Send status update
        E-->>C: Status notification
    else Exam failed
        S-->>C: Application rejected
    end
```

## 2. Admin User Management Swimlane

```mermaid
sequenceDiagram
    participant A as Administrator
    participant AS as Admin System
    participant DB as Database
    participant L as Logging System
    participant U as User System
    participant E as Email Service
    
    Note over A,E: Admin User Management Process
    
    A->>AS: Login with admin credentials
    AS->>DB: Validate admin credentials
    DB-->>AS: Authentication result
    
    alt Authentication successful
        AS-->>A: Grant admin access
        A->>AS: Request user management
        AS->>DB: Query user database
        DB-->>AS: Return user list
        AS-->>A: Display user management interface
        
        A->>AS: Select user action
        AS->>DB: Execute user operation
        
        alt User action successful
            AS->>L: Log admin activity
            AS->>U: Update user system
            AS->>E: Send notification if needed
            E-->>U: Email notification
            AS-->>A: Success confirmation
        else User action failed
            AS-->>A: Error message
        end
    else Authentication failed
        AS-->>A: Access denied
    end
```

## 3. Interview Scheduling Swimlane

```mermaid
sequenceDiagram
    participant Co as Company
    participant S as System
    participant DB as Database
    participant E as Email Service
    participant C as Candidate
    participant Cal as Calendar System
    
    Note over Co,Cal: Interview Scheduling Process
    
    Co->>S: Request to schedule interview
    S->>DB: Get candidate details
    DB-->>S: Return candidate info
    S-->>Co: Display candidate information
    
    Co->>S: Select interview date/time
    S->>Cal: Check availability
    Cal-->>S: Availability status
    
    alt Time slot available
        S->>DB: Create interview record
        S->>E: Send interview invitation
        E-->>C: Email invitation
        S-->>Co: Interview scheduled confirmation
        
        C->>S: Respond to invitation
        
        alt Candidate accepts
            S->>DB: Update interview status
            S->>E: Send confirmation to both parties
            E-->>Co: Confirmation email
            E-->>C: Confirmation email
            S->>Cal: Block calendar time
        else Candidate declines
            S->>DB: Update interview status
            S->>E: Send notification
            E-->>Co: Decline notification
        else Candidate requests reschedule
            S-->>Co: Reschedule request
            Co->>S: Propose new time
            S->>E: Send new invitation
            E-->>C: Updated invitation
        end
    else Time slot unavailable
        S-->>Co: Suggest alternative times
    end
```

## 4. AI Matching Process Swimlane

```mermaid
sequenceDiagram
    participant Co as Company
    participant S as System
    participant AI as AI Engine
    participant DB as Database
    participant A as Analytics
    
    Note over Co,A: AI Matching Process
    
    Co->>S: Request AI matching
    S->>Co: Display matching form
    Co->>S: Enter job criteria
    S->>DB: Query candidate database
    DB-->>S: Return candidate data
    
    S->>AI: Send job requirements and candidate data
    AI->>AI: Analyze skills compatibility
    AI->>AI: Calculate experience match
    AI->>AI: Evaluate education fit
    AI->>AI: Check location preference
    AI->>AI: Generate compatibility scores
    AI-->>S: Return ranked candidates
    
    S->>DB: Store matching results
    S->>A: Log matching activity
    S-->>Co: Display ranked candidate list
    
    Co->>S: Select candidate for interview
    S->>DB: Update candidate status
    S->>A: Log selection activity
    S-->>Co: Confirmation of selection
```

## 5. Exam System Swimlane

```mermaid
sequenceDiagram
    participant C as Candidate
    participant S as System
    participant DB as Database
    participant E as Email Service
    participant Co as Company
    participant Timer as Timer Service
    
    Note over C,Timer: Exam System Process
    
    C->>S: Access exam
    S->>DB: Check exam eligibility
    DB-->>S: Eligibility status
    
    alt Candidate eligible
        S->>DB: Load exam questions
        DB-->>S: Return questions
        S->>Timer: Start exam timer
        S-->>C: Display exam interface
        
        loop For each question
            C->>S: Submit answer
            S->>DB: Store answer
            S->>Timer: Check remaining time
            Timer-->>S: Time status
        end
        
        alt Time expired
            Timer->>S: Time up signal
            S->>DB: Auto-submit exam
        else Candidate submits
            C->>S: Manual submit
            S->>DB: Submit exam
        end
        
        S->>DB: Calculate exam score
        DB-->>S: Return results
        S->>DB: Store exam results
        S->>E: Send results notification
        E-->>Co: Results email
        S-->>C: Display exam results
    else Candidate not eligible
        S-->>C: Access denied message
    end
```

## 6. System Monitoring Swimlane

```mermaid
sequenceDiagram
    participant A as Administrator
    participant MS as Monitoring System
    participant DB as Database
    participant L as Logging System
    participant AS as Alert System
    participant S as System Services
    
    Note over A,S: System Monitoring Process
    
    A->>MS: Access monitoring dashboard
    MS->>DB: Query system metrics
    DB-->>MS: Return performance data
    MS->>L: Query error logs
    L-->>MS: Return log data
    MS-->>A: Display system status
    
    loop Continuous monitoring
        MS->>S: Check service health
        S-->>MS: Health status
        
        alt Service unhealthy
            MS->>AS: Trigger alert
            AS->>A: Send alert notification
            A->>MS: Acknowledge alert
            A->>S: Take corrective action
        end
        
        MS->>DB: Update monitoring data
        MS->>L: Log monitoring activity
    end
    
    A->>MS: Request detailed report
    MS->>DB: Query historical data
    DB-->>MS: Return historical metrics
    MS->>L: Query activity logs
    L-->>MS: Return log history
    MS-->>A: Generate comprehensive report
```

## 7. File Upload Process Swimlane

```mermaid
sequenceDiagram
    participant U as User
    participant S as System
    participant VS as Virus Scanner
    participant FS as File Storage
    participant DB as Database
    participant T as Thumbnail Generator
    
    Note over U,T: File Upload Process
    
    U->>S: Select file to upload
    S->>S: Validate file type
    S->>S: Check file size
    
    alt File valid
        S->>VS: Scan for viruses
        VS-->>S: Scan result
        
        alt File clean
            S->>S: Generate unique filename
            S->>FS: Upload file
            FS-->>S: Return file URL
            
            alt File is image
                S->>T: Generate thumbnail
                T-->>S: Return thumbnail URL
            end
            
            S->>DB: Store file metadata
            DB-->>S: Confirm storage
            S-->>U: Upload success notification
        else File infected
            S-->>U: Upload rejected - virus detected
        end
    else File invalid
        S-->>U: Upload rejected - invalid file
    end
```

## 8. Notification System Swimlane

```mermaid
sequenceDiagram
    participant S as System
    participant NS as Notification Service
    participant E as Email Service
    participant DB as Database
    participant U as User
    participant A as Admin
    
    Note over S,A: Notification System Process
    
    S->>NS: Trigger notification event
    NS->>DB: Get user preferences
    DB-->>NS: Return preferences
    
    alt Email notification enabled
        NS->>E: Send email notification
        E->>E: Process email queue
        E-->>U: Deliver email
        E-->>NS: Delivery status
        NS->>DB: Log delivery status
    end
    
    alt System notification enabled
        NS->>DB: Store system notification
        DB-->>NS: Confirm storage
        NS-->>U: Display notification
    end
    
    alt Admin notification required
        NS->>E: Send admin alert
        E-->>A: Admin notification
        NS->>DB: Log admin notification
    end
    
    NS->>DB: Update notification history
    NS-->>S: Notification sent confirmation
```

## Swimlane Descriptions

### Candidate Swimlane
- **Responsibilities**: User interactions, form submissions, exam taking
- **Key Activities**: Job searching, application submission, exam participation
- **Decision Points**: Job selection, exam answers, interview responses

### Company Swimlane
- **Responsibilities**: Job posting, candidate review, interview scheduling
- **Key Activities**: Job management, application review, candidate selection
- **Decision Points**: Job requirements, candidate selection, interview scheduling

### Administrator Swimlane
- **Responsibilities**: System management, user oversight, configuration
- **Key Activities**: User management, system monitoring, settings configuration
- **Decision Points**: User actions, system responses, maintenance scheduling

### System Swimlane
- **Responsibilities**: Data processing, business logic, integration
- **Key Activities**: Data validation, processing, storage, retrieval
- **Decision Points**: Data validation, process routing, error handling

### Database Swimlane
- **Responsibilities**: Data persistence, query processing, data integrity
- **Key Activities**: Data storage, retrieval, updates, transactions
- **Decision Points**: Query optimization, transaction management

### Email Service Swimlane
- **Responsibilities**: Email delivery, notification management
- **Key Activities**: Email processing, delivery tracking, bounce handling
- **Decision Points**: Delivery routing, retry logic, failure handling

### AI Engine Swimlane
- **Responsibilities**: Machine learning, pattern recognition, scoring
- **Key Activities**: Data analysis, algorithm processing, result generation
- **Decision Points**: Algorithm selection, scoring thresholds, result ranking

## Synchronization Points

### Critical Synchronization
- **Exam Submission**: Timer and submission must be synchronized
- **Interview Confirmation**: Both parties must confirm
- **File Upload**: Upload and metadata storage must be atomic
- **User Management**: Admin actions must be logged and applied atomically

### Asynchronous Operations
- **Email Delivery**: Can be processed asynchronously
- **File Processing**: Thumbnail generation can be background
- **Analytics**: Data analysis can be scheduled
- **Notifications**: Can be queued and processed later

### Error Handling
- **Transaction Rollback**: Database operations must be reversible
- **Retry Logic**: Failed operations should be retried
- **Fallback Mechanisms**: Alternative paths for critical operations
- **Audit Trails**: All operations must be logged for debugging
