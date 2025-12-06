# State Diagram - CandiHire System

## Overview
State diagrams show the different states of objects in the CandiHire system and the transitions between them.

## 1. User Account State Diagram

```mermaid
stateDiagram-v2
    [*] --> Unregistered : User visits site
    
    Unregistered --> PendingVerification : User registers
    PendingVerification --> Active : Email verified
    PendingVerification --> Unregistered : Verification expired
    
    Active --> Suspended : Admin suspends account
    Active --> Inactive : User deactivates account
    Active --> Deleted : Admin deletes account
    
    Suspended --> Active : Admin reactivates account
    Suspended --> Deleted : Admin deletes account
    
    Inactive --> Active : User reactivates account
    Inactive --> Deleted : Admin deletes account
    
    Deleted --> [*] : Account permanently removed
    
    note right of PendingVerification
        User receives verification email
        Account limited functionality
    end note
    
    note right of Active
        Full system access
        Can apply for jobs
        Can post jobs (companies)
    end note
    
    note right of Suspended
        Account temporarily disabled
        Cannot login
        Admin can reactivate
    end note
    
    note right of Inactive
        User-initiated deactivation
        Can reactivate anytime
        Data preserved
    end note
```

## 2. Job Application State Diagram

```mermaid
stateDiagram-v2
    [*] --> Draft : Candidate starts application
    
    Draft --> Submitted : Candidate submits
    Draft --> Cancelled : Candidate cancels
    
    Submitted --> UnderReview : Company reviews
    Submitted --> Rejected : Auto-rejected (not qualified)
    
    UnderReview --> Shortlisted : Company shortlists
    UnderReview --> Rejected : Company rejects
    UnderReview --> InterviewScheduled : Company schedules interview
    
    Shortlisted --> InterviewScheduled : Company schedules interview
    Shortlisted --> Rejected : Company rejects
    
    InterviewScheduled --> InterviewCompleted : Interview conducted
    InterviewScheduled --> InterviewCancelled : Interview cancelled
    
    InterviewCompleted --> OfferMade : Company makes offer
    InterviewCompleted --> Rejected : Company rejects after interview
    
    OfferMade --> Accepted : Candidate accepts offer
    OfferMade --> Declined : Candidate declines offer
    OfferMade --> Expired : Offer expires
    
    Accepted --> Hired : Candidate joins
    Hired --> [*] : Process complete
    
    Rejected --> [*] : Process complete
    Declined --> [*] : Process complete
    Expired --> [*] : Process complete
    InterviewCancelled --> [*] : Process complete
    Cancelled --> [*] : Process complete
    
    note right of Draft
        Application being prepared
        Can be saved and resumed
    end note
    
    note right of UnderReview
        Company evaluating application
        May request additional info
    end note
    
    note right of InterviewScheduled
        Interview date/time confirmed
        Both parties notified
    end note
    
    note right of OfferMade
        Company extends job offer
        Candidate has time to decide
    end note
```

## 3. Exam State Diagram

```mermaid
stateDiagram-v2
    [*] --> NotAssigned : Exam created
    
    NotAssigned --> Assigned : Admin assigns to job
    Assigned --> Available : Assignment activated
    
    Available --> InProgress : Candidate starts exam
    Available --> Expired : Assignment expires
    
    InProgress --> Submitted : Candidate submits
    InProgress --> TimeExpired : Time limit reached
    InProgress --> Abandoned : Candidate abandons
    
    Submitted --> Graded : System grades exam
    TimeExpired --> Graded : Auto-submit and grade
    Abandoned --> Failed : Mark as failed
    
    Graded --> Passed : Score meets threshold
    Graded --> Failed : Score below threshold
    
    Passed --> [*] : Exam complete
    Failed --> [*] : Exam complete
    Expired --> [*] : Exam complete
    
    note right of NotAssigned
        Exam created but not linked
        to any job posting
    end note
    
    note right of InProgress
        Candidate actively taking exam
        Timer running
        Answers being saved
    end note
    
    note right of Graded
        System calculates final score
        Compares against passing criteria
    end note
```

## 4. Interview State Diagram

```mermaid
stateDiagram-v2
    [*] --> Requested : Company requests interview
    
    Requested --> Scheduled : Time confirmed
    Requested --> Declined : Candidate declines
    Requested --> Rescheduled : Time changed
    
    Scheduled --> InProgress : Interview starts
    Scheduled --> Cancelled : Interview cancelled
    Scheduled --> Rescheduled : Time changed again
    
    InProgress --> Completed : Interview finished
    InProgress --> Cancelled : Interview cancelled during
    
    Completed --> FeedbackSubmitted : Company submits feedback
    Completed --> NoFeedback : No feedback submitted
    
    FeedbackSubmitted --> [*] : Process complete
    NoFeedback --> [*] : Process complete
    Cancelled --> [*] : Process complete
    Declined --> [*] : Process complete
    
    Rescheduled --> Scheduled : New time confirmed
    Rescheduled --> Declined : Candidate declines reschedule
    
    note right of Requested
        Company wants to interview
        candidate
        Time not yet confirmed
    end note
    
    note right of Scheduled
        Date and time confirmed
        Both parties notified
        Meeting details provided
    end note
    
    note right of InProgress
        Interview actively happening
        May be virtual or in-person
    end note
```

## 5. System Maintenance State Diagram

```mermaid
stateDiagram-v2
    [*] --> Running : System operational
    
    Running --> MaintenanceMode : Admin initiates maintenance
    Running --> Error : System error occurs
    Running --> Overloaded : High load detected
    
    MaintenanceMode --> MaintenanceInProgress : Maintenance starts
    MaintenanceInProgress --> Running : Maintenance complete
    MaintenanceInProgress --> Error : Maintenance fails
    
    Error --> Diagnosing : Error detected
    Diagnosing --> Recovering : Solution identified
    Diagnosing --> CriticalError : Critical failure
    
    Recovering --> Running : Recovery successful
    Recovering --> Error : Recovery failed
    
    CriticalError --> EmergencyMaintenance : Emergency response
    EmergencyMaintenance --> Running : Emergency resolved
    EmergencyMaintenance --> Shutdown : Cannot recover
    
    Overloaded --> Scaling : Auto-scaling triggered
    Scaling --> Running : Scaling complete
    Scaling --> Overloaded : Scaling insufficient
    
    Shutdown --> [*] : System offline
    
    note right of MaintenanceMode
        Planned maintenance
        Users notified
        Limited functionality
    end note
    
    note right of Error
        System error detected
        Automatic recovery attempted
        Monitoring alerts triggered
    end note
    
    note right of CriticalError
        Critical system failure
        Manual intervention required
        Emergency procedures activated
    end note
```

## 6. Admin Session State Diagram

```mermaid
stateDiagram-v2
    [*] --> LoggedOut : Admin not logged in
    
    LoggedOut --> LoggingIn : Admin enters credentials
    LoggingIn --> Authenticated : Credentials valid
    LoggingIn --> LoggedOut : Credentials invalid
    
    Authenticated --> Active : Session active
    Active --> Idle : No activity timeout
    Active --> LoggedOut : Admin logs out
    
    Idle --> Active : Admin activity
    Idle --> LoggedOut : Session timeout
    
    Active --> Suspended : Account suspended
    Suspended --> LoggedOut : Forced logout
    
    note right of LoggingIn
        Validating admin credentials
        Checking account status
        Creating session
    end note
    
    note right of Active
        Admin actively using system
        Full administrative access
        All features available
    end note
    
    note right of Idle
        No activity for timeout period
        Session may expire soon
        Warning notifications sent
    end note
```

## 7. File Upload State Diagram

```mermaid
stateDiagram-v2
    [*] --> Selecting : User selects file
    
    Selecting --> Validating : File selected
    Selecting --> Cancelled : User cancels
    
    Validating --> Uploading : File valid
    Validating --> Rejected : File invalid
    
    Uploading --> Processing : Upload complete
    Uploading --> Failed : Upload failed
    
    Processing --> Completed : Processing successful
    Processing --> Failed : Processing failed
    
    Completed --> [*] : File ready
    Failed --> [*] : Upload failed
    Rejected --> [*] : File rejected
    Cancelled --> [*] : Upload cancelled
    
    note right of Validating
        Check file type
        Check file size
        Scan for viruses
    end note
    
    note right of Processing
        Generate thumbnails
        Extract metadata
        Store in database
    end note
    
    note right of Completed
        File available for use
        User notified
        Metadata stored
    end note
```

## 8. Notification State Diagram

```mermaid
stateDiagram-v2
    [*] --> Created : Notification created
    
    Created --> Queued : Added to queue
    Queued --> Sending : Being sent
    Queued --> Cancelled : Cancelled
    
    Sending --> Delivered : Successfully sent
    Sending --> Failed : Send failed
    Sending --> Retrying : Retry needed
    
    Retrying --> Sending : Retry attempt
    Retrying --> Failed : Max retries reached
    
    Delivered --> Read : User reads notification
    Delivered --> Expired : Notification expires
    
    Failed --> [*] : Notification failed
    Cancelled --> [*] : Notification cancelled
    Read --> [*] : Notification complete
    Expired --> [*] : Notification expired
    
    note right of Queued
        Notification in send queue
        Waiting for processing
        May have priority
    end note
    
    note right of Sending
        Actively being sent
        Email service processing
        Delivery attempt in progress
    end note
    
    note right of Retrying
        Previous send failed
        Retry with backoff
        Limited retry attempts
    end note
```

## State Transition Rules

### User Account States
- **Registration**: Unregistered → PendingVerification
- **Verification**: PendingVerification → Active (success) or Unregistered (expired)
- **Suspension**: Active → Suspended (admin action)
- **Deactivation**: Active → Inactive (user action)
- **Deletion**: Any state → Deleted (admin action)

### Job Application States
- **Submission**: Draft → Submitted
- **Review**: Submitted → UnderReview
- **Decision**: UnderReview → Shortlisted/Rejected/InterviewScheduled
- **Interview**: InterviewScheduled → InterviewCompleted
- **Offer**: InterviewCompleted → OfferMade
- **Acceptance**: OfferMade → Accepted → Hired

### Exam States
- **Assignment**: NotAssigned → Assigned → Available
- **Taking**: Available → InProgress
- **Completion**: InProgress → Submitted/TimeExpired/Abandoned
- **Grading**: Submitted/TimeExpired → Graded
- **Result**: Graded → Passed/Failed

### Interview States
- **Scheduling**: Requested → Scheduled
- **Conducting**: Scheduled → InProgress
- **Completion**: InProgress → Completed
- **Feedback**: Completed → FeedbackSubmitted

## State Validation Rules

### Preconditions
- **User Registration**: Valid email, unique username
- **Job Application**: User authenticated, job exists, requirements met
- **Exam Taking**: User assigned, exam available, not expired
- **Interview Scheduling**: Both parties available, valid time slot

### Postconditions
- **State Changes**: Logged in audit trail
- **Notifications**: Sent to relevant parties
- **Data Updates**: Database updated atomically
- **Cleanup**: Temporary data removed

### Error Handling
- **Invalid Transitions**: Prevented by validation
- **Concurrent Updates**: Handled with locking
- **System Failures**: Rollback to previous state
- **Timeout Handling**: Automatic state transitions
