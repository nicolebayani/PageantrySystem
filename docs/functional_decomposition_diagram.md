# Pageant Tabulating System - Functional Decomposition Diagram

## System Overview
```
Pageant Tabulating System
├── Authentication Module
├── Admin Module
├── Judge Module
├── Scoring Engine
└── Reporting Module
```

## Detailed Functional Breakdown

### 1. Authentication Module
```
Authentication
├── User Login
│   ├── Admin Login
│   ├── Judge Login
│   └── Session Management
├── User Registration
│   ├── Admin Registration
│   └── Judge Registration
└── Security
    ├── Password Validation
    ├── Role-based Access Control
    └── Session Security
```

### 2. Admin Module
```
Admin Functions
├── Dashboard
│   ├── System Statistics
│   ├── Judge Progress Tracking
│   ├── Recent Activity Log
│   └── Quick Actions Menu
├── Candidate Management
│   ├── Add New Candidate
│   ├── Edit Candidate Info
│   ├── Delete Candidate
│   ├── View Candidate List
│   └── Upload Candidate Photos
├── Criteria Management
│   ├── Add Judging Criteria
│   ├── Edit Criteria Details
│   ├── Set Percentage Weights
│   ├── Delete Criteria
│   └── Validate Total Percentage (100%)
├── Judge Management
│   ├── Create Judge Accounts
│   ├── Edit Judge Information
│   ├── Deactivate Judges
│   ├── Reset Judge Passwords
│   └── Monitor Judge Activity
├── Results & Analytics
│   ├── Real-time Score Calculation
│   ├── Ranking Generation
│   ├── Detailed Score Breakdown
│   ├── Winner Declaration
│   ├── Export Results
│   └── Print Certificates
└── System Settings
    ├── Pageant Configuration
    ├── Scoring Rules
    ├── System Preferences
    └── Backup & Restore
```

### 3. Judge Module
```
Judge Functions
├── Judge Dashboard
│   ├── Personal Scoring Progress
│   ├── Assigned Candidates
│   ├── Criteria Overview
│   └── Submission Status
├── Scoring Interface
│   ├── Candidate Selection
│   ├── Criteria-based Scoring (1-10)
│   ├── Score Validation
│   ├── Auto-save Functionality
│   ├── Score Modification
│   └── Final Submission
└── Profile Management
    ├── View Personal Info
    ├── Change Password
    └── View Scoring History
```

### 4. Scoring Engine
```
Scoring System
├── Score Collection
│   ├── Individual Judge Scores
│   ├── Score Validation Rules
│   ├── Duplicate Prevention
│   └── Data Integrity Checks
├── Score Calculation
│   ├── Average Score per Criteria
│   ├── Weighted Score Application
│   ├── Final Score Computation
│   └── Ranking Algorithm
├── Real-time Updates
│   ├── Live Score Tracking
│   ├── Progress Monitoring
│   ├── Automatic Recalculation
│   └── Status Notifications
└── Data Management
    ├── Score Storage
    ├── Audit Trail
    ├── Backup Scores
    └── Score Recovery
```

### 5. Reporting Module
```
Reports & Analytics
├── Score Reports
│   ├── Individual Candidate Scores
│   ├── Judge-wise Scoring
│   ├── Criteria-wise Analysis
│   └── Comparative Rankings
├── Progress Reports
│   ├── Completion Status
│   ├── Judge Participation
│   ├── Scoring Timeline
│   └── System Usage Stats
├── Winner Reports
│   ├── Final Rankings
│   ├── Winner Certificates
│   ├── Award Categories
│   └── Achievement Records
└── Export Functions
    ├── PDF Generation
    ├── Excel Export
    ├── CSV Downloads
    └── Print Layouts
```

## Menu Structure

### Admin Navigation Menu
```
Main Menu (Admin)
├── 🏠 Dashboard
├── 👥 Candidates
│   ├── View All Candidates
│   ├── Add New Candidate
│   └── Bulk Import
├── 📋 Criteria
│   ├── Manage Criteria
│   ├── Set Percentages
│   └── Validation Rules
├── ⚖️ Judges
│   ├── Judge Accounts
│   ├── Assign Judges
│   └── Monitor Progress
├── 🏆 Results
│   ├── Live Results
│   ├── Final Rankings
│   ├── Winner Declaration
│   └── Export Reports
├── ⚙️ Settings
│   ├── Pageant Settings
│   ├── System Config
│   └── Backup/Restore
└── 🚪 Logout
```

### Judge Navigation Menu
```
Main Menu (Judge)
├── 🏠 Dashboard
├── ⭐ Score Candidates
│   ├── Select Candidate
│   ├── Score by Criteria
│   └── Review Scores
├── 📊 My Progress
│   ├── Completion Status
│   ├── Scoring History
│   └── Pending Scores
├── 👤 Profile
│   ├── Personal Info
│   └── Change Password
└── 🚪 Logout
```

## System Flow Diagram
```
Start
  ↓
Login Authentication
  ↓
Role Check
  ├── Admin → Admin Dashboard → Admin Functions
  └── Judge → Judge Dashboard → Scoring Functions
  ↓
Data Processing
  ├── Score Collection
  ├── Validation
  ├── Calculation
  └── Storage
  ↓
Results Generation
  ├── Real-time Updates
  ├── Rankings
  └── Reports
  ↓
End/Logout
```

## Data Flow
```
Input Data → Validation → Processing → Storage → Output
    ↓           ↓           ↓          ↓        ↓
Scores      Rules      Calculate   Database  Results
Photos      Checks     Weighted    Backup    Reports
Criteria    Format     Averages    Audit     Rankings
Users       Security   Rankings    Log       Exports
```
