# Activity Diagram - CandiHire System

## Overview
Activity diagrams show the flow of activities and decision points in the CandiHire system processes.

## 1. User Registration Process

```mermaid
flowchart TD
    Start([User visits registration page])
    SelectType{Select user type}
    Candidate[Fill candidate form]
    Company[Fill company form]
    Admin[Admin registration<br/>(System only)]
    
    ValidateData{Validate form data}
    DataValid{Data valid?}
    ShowErrors[Display validation errors]
    CheckEmail{Email already exists?}
    CreateAccount[Create user account]
    HashPassword[Hash password]
    StoreData[Store in database]
    SendConfirmation[Send confirmation email]
    Success[Registration successful]
    End([End])
    
    Start --> SelectType
    SelectType -->|Candidate| Candidate
    SelectType -->|Company| Company
    SelectType -->|Admin| Admin
    
    Candidate --> ValidateData
    Company --> ValidateData
    Admin --> ValidateData
    
    ValidateData --> DataValid
    DataValid -->|No| ShowErrors
    ShowErrors --> Candidate
    ShowErrors --> Company
    ShowErrors --> Admin
    
    DataValid -->|Yes| CheckEmail
    CheckEmail -->|Yes| ShowErrors
    CheckEmail -->|No| CreateAccount
    CreateAccount --> HashPassword
    HashPassword --> StoreData
    StoreData --> SendConfirmation
    SendConfirmation --> Success
    Success --> End
```

## 2. Job Application Process

```mermaid
flowchart TD
    Start([Candidate searches for jobs])
    ViewJob[View job details]
    CheckRequirements{Meets job requirements?}
    ApplyJob[Click apply button]
    CheckExam{Job requires exam?}
    TakeExam[Take required exam]
    ExamPassed{Exam passed?}
    SubmitApplication[Submit application]
    NotifyCompany[Notify company]
    UpdateStatus[Update application status]
    SendConfirmation[Send confirmation to candidate]
    End([End])
    
    Start --> ViewJob
    ViewJob --> CheckRequirements
    CheckRequirements -->|No| Start
    CheckRequirements -->|Yes| ApplyJob
    ApplyJob --> CheckExam
    CheckExam -->|No| SubmitApplication
    CheckExam -->|Yes| TakeExam
    TakeExam --> ExamPassed
    ExamPassed -->|No| Start
    ExamPassed -->|Yes| SubmitApplication
    SubmitApplication --> NotifyCompany
    NotifyCompany --> UpdateStatus
    UpdateStatus --> SendConfirmation
    SendConfirmation --> End
```

## 3. AI Matching Process

```mermaid
flowchart TD
    Start([Company requests AI matching])
    InputCriteria[Input job criteria]
    GetCandidates[Retrieve candidate database]
    FilterActive[Filter active candidates]
    AnalyzeSkills[Analyze skills compatibility]
    CheckExperience[Check experience match]
    CalculateEducation[Calculate education score]
    LocationMatch[Check location preference]
    CalculateScore[Calculate overall compatibility score]
    RankCandidates[Rank candidates by score]
    FilterTop[Filter top candidates]
    GenerateReport[Generate matching report]
    DisplayResults[Display results to company]
    CompanyAction{Company action?}
    ScheduleInterview[Schedule interview]
    RejectCandidate[Reject candidate]
    SaveNotes[Save company notes]
    End([End])
    
    Start --> InputCriteria
    InputCriteria --> GetCandidates
    GetCandidates --> FilterActive
    FilterActive --> AnalyzeSkills
    AnalyzeSkills --> CheckExperience
    CheckExperience --> CalculateEducation
    CalculateEducation --> LocationMatch
    LocationMatch --> CalculateScore
    CalculateScore --> RankCandidates
    RankCandidates --> FilterTop
    FilterTop --> GenerateReport
    GenerateReport --> DisplayResults
    DisplayResults --> CompanyAction
    CompanyAction -->|Interview| ScheduleInterview
    CompanyAction -->|Reject| RejectCandidate
    CompanyAction -->|Notes| SaveNotes
    ScheduleInterview --> End
    RejectCandidate --> End
    SaveNotes --> End
```

## 4. Admin User Management Process

```mermaid
flowchart TD
    Start([Admin logs in])
    ViewDashboard[View admin dashboard]
    SelectManagement[Select user management]
    ChooseUserType{Select user type}
    LoadUsers[Load user list]
    DisplayUsers[Display users with actions]
    AdminAction{Admin action?}
    
    ViewDetails[View user details]
    EditUser[Edit user information]
    ToggleStatus[Toggle user status]
    DeleteUser[Delete user]
    BulkAction[Perform bulk action]
    SearchUsers[Search users]
    ExportData[Export user data]
    
    ConfirmAction{Confirm action?}
    ExecuteAction[Execute action]
    LogActivity[Log admin activity]
    UpdateUI[Update user interface]
    ShowNotification[Show success notification]
    End([End])
    
    Start --> ViewDashboard
    ViewDashboard --> SelectManagement
    SelectManagement --> ChooseUserType
    ChooseUserType -->|Candidates| LoadUsers
    ChooseUserType -->|Companies| LoadUsers
    ChooseUserType -->|Admins| LoadUsers
    LoadUsers --> DisplayUsers
    DisplayUsers --> AdminAction
    
    AdminAction -->|View| ViewDetails
    AdminAction -->|Edit| EditUser
    AdminAction -->|Toggle| ToggleStatus
    AdminAction -->|Delete| DeleteUser
    AdminAction -->|Bulk| BulkAction
    AdminAction -->|Search| SearchUsers
    AdminAction -->|Export| ExportData
    
    ViewDetails --> DisplayUsers
    EditUser --> ConfirmAction
    ToggleStatus --> ConfirmAction
    DeleteUser --> ConfirmAction
    BulkAction --> ConfirmAction
    SearchUsers --> LoadUsers
    ExportData --> End
    
    ConfirmAction -->|Yes| ExecuteAction
    ConfirmAction -->|No| DisplayUsers
    ExecuteAction --> LogActivity
    LogActivity --> UpdateUI
    UpdateUI --> ShowNotification
    ShowNotification --> DisplayUsers
```

## 5. Exam Taking Process

```mermaid
flowchart TD
    Start([Candidate accesses exam])
    CheckEligibility{Is candidate eligible?}
    LoadExam[Load exam questions]
    DisplayInstructions[Display exam instructions]
    StartTimer[Start exam timer]
    QuestionLoop{More questions?}
    DisplayQuestion[Display current question]
    CandidateAnswer[Candidate answers question]
    ValidateAnswer{Answer valid?}
    SaveAnswer[Save answer]
    NextQuestion[Move to next question]
    TimeUp{Time up?}
    AutoSubmit[Auto-submit exam]
    ManualSubmit[Manual submit]
    CalculateScore[Calculate exam score]
    StoreResults[Store exam results]
    NotifyCompany[Notify company of results]
    DisplayResults[Display results to candidate]
    End([End])
    
    Start --> CheckEligibility
    CheckEligibility -->|No| End
    CheckEligibility -->|Yes| LoadExam
    LoadExam --> DisplayInstructions
    DisplayInstructions --> StartTimer
    StartTimer --> QuestionLoop
    QuestionLoop -->|Yes| DisplayQuestion
    QuestionLoop -->|No| CalculateScore
    DisplayQuestion --> CandidateAnswer
    CandidateAnswer --> ValidateAnswer
    ValidateAnswer -->|No| DisplayQuestion
    ValidateAnswer -->|Yes| SaveAnswer
    SaveAnswer --> NextQuestion
    NextQuestion --> TimeUp
    TimeUp -->|Yes| AutoSubmit
    TimeUp -->|No| QuestionLoop
    AutoSubmit --> CalculateScore
    ManualSubmit --> CalculateScore
    CalculateScore --> StoreResults
    StoreResults --> NotifyCompany
    NotifyCompany --> DisplayResults
    DisplayResults --> End
```

## 6. Interview Scheduling Process

```mermaid
flowchart TD
    Start([Company wants to schedule interview])
    SelectCandidate[Select candidate]
    CheckAvailability[Check candidate availability]
    SetDateTime[Set date and time]
    ChooseMode{Interview mode?}
    Virtual[Set up virtual meeting]
    Onsite[Set up onsite location]
    SendInvite[Send interview invitation]
    CandidateResponse{Candidate response?}
    Accept[Accept interview]
    Decline[Decline interview]
    Reschedule[Request reschedule]
    ConfirmInterview[Confirm interview details]
    SendReminder[Send reminder notifications]
    ConductInterview[Conduct interview]
    CollectFeedback[Collect interview feedback]
    UpdateStatus[Update application status]
    NotifyParties[Notify both parties]
    End([End])
    
    Start --> SelectCandidate
    SelectCandidate --> CheckAvailability
    CheckAvailability --> SetDateTime
    SetDateTime --> ChooseMode
    ChooseMode -->|Virtual| Virtual
    ChooseMode -->|Onsite| Onsite
    Virtual --> SendInvite
    Onsite --> SendInvite
    SendInvite --> CandidateResponse
    CandidateResponse -->|Accept| Accept
    CandidateResponse -->|Decline| Decline
    CandidateResponse -->|Reschedule| Reschedule
    Accept --> ConfirmInterview
    Reschedule --> SetDateTime
    Decline --> UpdateStatus
    ConfirmInterview --> SendReminder
    SendReminder --> ConductInterview
    ConductInterview --> CollectFeedback
    CollectFeedback --> UpdateStatus
    UpdateStatus --> NotifyParties
    NotifyParties --> End
```

## 7. System Monitoring Process (Admin)

```mermaid
flowchart TD
    Start([Admin accesses monitoring])
    LoadMetrics[Load system metrics]
    CheckPerformance{System performance OK?}
    CheckErrors{Any errors?}
    CheckUsers{User activity normal?}
    CheckStorage{Storage space OK?}
    GenerateReport[Generate monitoring report]
    AlertAdmin[Alert admin if issues]
    TakeAction{Take action?}
    RestartService[Restart service]
    ClearCache[Clear cache]
    ScaleResources[Scale resources]
    UpdateLogs[Update system logs]
    ScheduleMaintenance[Schedule maintenance]
    End([End])
    
    Start --> LoadMetrics
    LoadMetrics --> CheckPerformance
    CheckPerformance -->|No| AlertAdmin
    CheckPerformance -->|Yes| CheckErrors
    CheckErrors -->|Yes| AlertAdmin
    CheckErrors -->|No| CheckUsers
    CheckUsers -->|No| AlertAdmin
    CheckUsers -->|Yes| CheckStorage
    CheckStorage -->|No| AlertAdmin
    CheckStorage -->|Yes| GenerateReport
    AlertAdmin --> TakeAction
    TakeAction -->|Restart| RestartService
    TakeAction -->|Cache| ClearCache
    TakeAction -->|Scale| ScaleResources
    TakeAction -->|Logs| UpdateLogs
    TakeAction -->|Maintenance| ScheduleMaintenance
    RestartService --> UpdateLogs
    ClearCache --> UpdateLogs
    ScaleResources --> UpdateLogs
    UpdateLogs --> GenerateReport
    ScheduleMaintenance --> GenerateReport
    GenerateReport --> End
```

## 8. File Upload Process

```mermaid
flowchart TD
    Start([User selects file to upload])
    ValidateFile{File type valid?}
    CheckSize{File size OK?}
    ScanVirus{Virus scan passed?}
    GenerateName[Generate unique filename]
    UploadFile[Upload to storage]
    CreateThumbnail[Create thumbnail if image]
    StoreMetadata[Store file metadata]
    UpdateProfile[Update user profile]
    SendConfirmation[Send upload confirmation]
    Error[Display error message]
    End([End])
    
    Start --> ValidateFile
    ValidateFile -->|No| Error
    ValidateFile -->|Yes| CheckSize
    CheckSize -->|No| Error
    CheckSize -->|Yes| ScanVirus
    ScanVirus -->|No| Error
    ScanVirus -->|Yes| GenerateName
    GenerateName --> UploadFile
    UploadFile --> CreateThumbnail
    CreateThumbnail --> StoreMetadata
    StoreMetadata --> UpdateProfile
    UpdateProfile --> SendConfirmation
    SendConfirmation --> End
    Error --> End
```

## Activity Descriptions

### Decision Points
- **User Type Selection**: Determines registration flow
- **Data Validation**: Ensures data integrity
- **Eligibility Checks**: Verifies user permissions
- **Time Constraints**: Manages exam timing
- **Response Handling**: Processes user choices

### Parallel Activities
- **File Processing**: Thumbnail generation and metadata storage
- **Notification Sending**: Email and system notifications
- **Logging**: Activity tracking and audit trails
- **Database Updates**: Multiple table updates

### Error Handling
- **Validation Errors**: Form validation failures
- **System Errors**: Database or service failures
- **Permission Errors**: Unauthorized access attempts
- **Timeout Errors**: Session or process timeouts

### Synchronization Points
- **Exam Submission**: Synchronizes timer and submission
- **Interview Confirmation**: Synchronizes both parties
- **Bulk Operations**: Synchronizes multiple user updates
- **System Maintenance**: Synchronizes all services
