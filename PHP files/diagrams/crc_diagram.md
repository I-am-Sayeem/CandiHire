# CRC (Class-Responsibility-Collaboration) Diagram - CandiHire System

## Overview
CRC diagrams show the classes, their responsibilities, and collaborations in the CandiHire system, including admin functionality.

## 1. User Management Classes

### User (Abstract Base Class)
```
┌─────────────────────────────────────┐
│                User                 │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Authenticate credentials          │
│ • Manage profile data               │
│ • Handle login/logout               │
│ • Validate user input               │
│ • Track activity                    │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Database (store/retrieve data)    │
│ • SessionManager (manage sessions)  │
│ • EmailService (send notifications) │
│ • FileManager (handle uploads)      │
└─────────────────────────────────────┘
```

### Candidate
```
┌─────────────────────────────────────┐
│              Candidate              │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Apply for jobs                    │
│ • Take online exams                 │
│ • Manage CV and documents           │
│ • Search job opportunities          │
│ • Participate in interviews         │
│ • Track application status          │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Job (apply for positions)         │
│ • Exam (take assessments)           │
│ • Application (submit applications) │
│ • Interview (participate)           │
│ • FileManager (upload CV)           │
│ • NotificationService (receive)     │
└─────────────────────────────────────┘
```

### Company
```
┌─────────────────────────────────────┐
│              Company                │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Post job openings                 │
│ • Review applications               │
│ • Schedule interviews               │
│ • Use AI matching                   │
│ • Manage company profile            │
│ • Create and assign exams           │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Job (create job postings)         │
│ • Application (review candidates)   │
│ • Interview (schedule meetings)     │
│ • Exam (create assessments)         │
│ • AIMatching (find candidates)      │
│ • NotificationService (send)        │
└─────────────────────────────────────┘
```

### Administrator
```
┌─────────────────────────────────────┐
│           Administrator             │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Manage all users                  │
│ • Monitor system performance        │
│ • Configure system settings         │
│ • View activity logs                │
│ • Handle bulk operations            │
│ • Generate reports                  │
│ • Maintain system security          │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • User (manage accounts)            │
│ • SystemMonitor (check health)      │
│ • SettingsManager (configure)       │
│ • LogManager (view logs)            │
│ • ReportGenerator (create reports)  │
│ • SecurityManager (maintain)        │
└─────────────────────────────────────┘
```

## 2. Core Business Classes

### Job
```
┌─────────────────────────────────────┐
│                Job                  │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Store job details                 │
│ • Validate job requirements         │
│ • Track application count           │
│ • Manage job status                 │
│ • Handle job search                 │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Company (job owner)               │
│ • Application (receive applications)│
│ • Exam (link to assessments)        │
│ • SearchEngine (enable searching)   │
│ • NotificationService (notify)      │
└─────────────────────────────────────┘
```

### Application
```
┌─────────────────────────────────────┐
│            Application              │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Store application data            │
│ • Track application status          │
│ • Link candidate to job             │
│ • Handle status updates             │
│ • Store application notes           │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Candidate (applicant)             │
│ • Job (applied position)            │
│ • Company (reviewer)                │
│ • StatusManager (update status)     │
│ • NotificationService (notify)      │
└─────────────────────────────────────┘
```

### Exam
```
┌─────────────────────────────────────┐
│                Exam                 │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Store exam questions              │
│ • Manage exam settings              │
│ • Track exam assignments            │
│ • Calculate scores                  │
│ • Handle time limits                │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Question (exam content)           │
│ • Assignment (link to jobs)         │
│ • Result (store scores)             │
│ • Timer (manage time limits)        │
│ • GradingEngine (calculate scores)  │
└─────────────────────────────────────┘
```

### Interview
```
┌─────────────────────────────────────┐
│             Interview               │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Schedule interview sessions       │
│ • Manage interview details          │
│ • Track interview status            │
│ • Store feedback                    │
│ • Handle rescheduling               │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Candidate (participant)           │
│ • Company (interviewer)             │
│ • Calendar (schedule management)    │
│ • NotificationService (send invites)│
│ • FeedbackManager (collect feedback)│
└─────────────────────────────────────┘
```

## 3. System Management Classes

### SessionManager
```
┌─────────────────────────────────────┐
│           SessionManager            │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Create user sessions              │
│ • Validate session tokens           │
│ • Handle session timeouts           │
│ • Manage user authentication        │
│ • Track user activity               │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • User (authenticate)               │
│ • Database (store sessions)         │
│ • SecurityManager (validate)        │
│ • ActivityLogger (track activity)   │
└─────────────────────────────────────┘
```

### Database
```
┌─────────────────────────────────────┐
│              Database               │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Store all system data             │
│ • Execute queries                   │
│ • Manage transactions               │
│ • Ensure data integrity             │
│ • Handle concurrent access          │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • All business classes              │
│ • TransactionManager (handle txns)  │
│ • BackupManager (backup data)       │
│ • IndexManager (optimize queries)   │
└─────────────────────────────────────┘
```

### NotificationService
```
┌─────────────────────────────────────┐
│        NotificationService          │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Send email notifications          │
│ • Queue notification requests       │
│ • Track delivery status             │
│ • Handle notification templates     │
│ • Manage notification preferences   │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • EmailService (send emails)        │
│ • User (notification recipients)    │
│ • TemplateManager (email templates) │
│ • DeliveryTracker (track status)    │
└─────────────────────────────────────┘
```

## 4. Admin-Specific Classes

### AdminDashboard
```
┌─────────────────────────────────────┐
│           AdminDashboard            │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Display system overview           │
│ • Show user statistics              │
│ • Present activity logs             │
│ • Handle admin navigation           │
│ • Manage dashboard widgets          │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • StatisticsCalculator (metrics)    │
│ • ActivityLogger (recent activity)  │
│ • UserManager (user data)           │
│ • ReportGenerator (reports)         │
└─────────────────────────────────────┘
```

### UserManager
```
┌─────────────────────────────────────┐
│            UserManager              │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Create user accounts              │
│ • Update user information           │
│ • Delete user accounts              │
│ • Toggle user status                │
│ • Perform bulk operations           │
│ • Search and filter users           │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • User (manage accounts)            │
│ • Database (store changes)          │
│ • ActivityLogger (log actions)      │
│ • NotificationService (notify)      │
└─────────────────────────────────────┘
```

### SystemMonitor
```
┌─────────────────────────────────────┐
│           SystemMonitor             │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Monitor system performance        │
│ • Track resource usage              │
│ • Detect system errors              │
│ • Generate health reports           │
│ • Alert on issues                   │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • PerformanceTracker (metrics)      │
│ • ErrorLogger (error tracking)      │
│ • AlertManager (send alerts)        │
│ • HealthChecker (system health)     │
└─────────────────────────────────────┘
```

### SettingsManager
```
┌─────────────────────────────────────┐
│          SettingsManager            │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Store system settings             │
│ • Update configuration              │
│ • Validate setting values           │
│ • Apply setting changes             │
│ • Track setting history             │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Database (store settings)         │
│ • ConfigurationValidator (validate) │
│ • ChangeTracker (track changes)     │
│ • SystemUpdater (apply changes)     │
└─────────────────────────────────────┘
```

## 5. AI and Matching Classes

### AIMatching
```
┌─────────────────────────────────────┐
│            AIMatching               │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Analyze candidate profiles        │
│ • Calculate compatibility scores    │
│ • Rank candidates by match          │
│ • Process job requirements          │
│ • Generate matching reports         │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Candidate (profile data)          │
│ • Job (requirements)                │
│ • ScoringAlgorithm (calculate)      │
│ • RankingEngine (rank results)      │
│ • ReportGenerator (create reports)  │
└─────────────────────────────────────┘
```

### ScoringAlgorithm
```
┌─────────────────────────────────────┐
│        ScoringAlgorithm             │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Calculate skill compatibility     │
│ • Evaluate experience match         │
│ • Score education fit               │
│ • Consider location preference      │
│ • Generate overall score            │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • Candidate (profile analysis)      │
│ • Job (requirement analysis)        │
│ • WeightCalculator (apply weights)  │
│ • ScoreNormalizer (normalize)       │
└─────────────────────────────────────┘
```

## 6. Utility Classes

### FileManager
```
┌─────────────────────────────────────┐
│            FileManager              │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Handle file uploads               │
│ • Validate file types               │
│ • Generate thumbnails               │
│ • Store file metadata               │
│ • Manage file access                │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • FileStorage (store files)         │
│ • VirusScanner (scan files)         │
│ • ThumbnailGenerator (create)       │
│ • MetadataExtractor (extract)       │
└─────────────────────────────────────┘
```

### SecurityManager
```
┌─────────────────────────────────────┐
│          SecurityManager            │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Validate user permissions         │
│ • Encrypt sensitive data            │
│ • Handle authentication             │
│ • Monitor security events           │
│ • Enforce access controls           │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • User (permission validation)      │
│ • EncryptionService (encrypt)       │
│ • AuditLogger (log events)          │
│ • AccessController (enforce)        │
└─────────────────────────────────────┘
```

### ActivityLogger
```
┌─────────────────────────────────────┐
│          ActivityLogger             │
├─────────────────────────────────────┤
│ Responsibilities:                   │
│ • Log user activities               │
│ • Track system events               │
│ • Store audit trails                │
│ • Generate activity reports         │
│ • Archive old logs                  │
├─────────────────────────────────────┤
│ Collaborations:                     │
│ • All business classes              │
│ • Database (store logs)             │
│ • LogArchiver (archive)             │
│ • ReportGenerator (reports)         │
└─────────────────────────────────────┘
```

## Class Relationships

### Inheritance Hierarchy
```
User (Abstract)
├── Candidate
├── Company
└── Administrator
```

### Composition Relationships
- **AdminDashboard** contains **UserManager**, **SystemMonitor**, **SettingsManager**
- **AIMatching** contains **ScoringAlgorithm**
- **NotificationService** contains **EmailService**
- **FileManager** contains **ThumbnailGenerator**, **VirusScanner**

### Collaboration Patterns
- **All classes** collaborate with **Database** for data persistence
- **All classes** collaborate with **ActivityLogger** for audit trails
- **User classes** collaborate with **SessionManager** for authentication
- **Admin classes** collaborate with **SecurityManager** for authorization

## Design Patterns Used

### Singleton Pattern
- **Database**: Single database connection
- **SessionManager**: Single session management instance
- **SecurityManager**: Single security management instance

### Factory Pattern
- **UserFactory**: Create different user types
- **NotificationFactory**: Create different notification types
- **ReportFactory**: Create different report types

### Observer Pattern
- **NotificationService**: Notify users of events
- **SystemMonitor**: Alert on system issues
- **ActivityLogger**: Log all system activities

### Strategy Pattern
- **ScoringAlgorithm**: Different scoring strategies
- **AuthenticationStrategy**: Different auth methods
- **NotificationStrategy**: Different notification methods
