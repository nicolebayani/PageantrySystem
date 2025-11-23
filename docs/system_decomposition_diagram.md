# PageantrySystem - Complete System Decomposition Diagram

## 🏗️ System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          PAGEANTRY TABULATING SYSTEM                        │
│                              (Web Application)                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                        │
                    ┌───────────────────┼───────────────────┐
                    │                   │                   │
            ┌───────▼────────┐ ┌────────▼────────┐ ┌────────▼────────┐
            │  PRESENTATION  │ │  BUSINESS LOGIC │ │   DATA LAYER    │
            │     LAYER      │ │      LAYER      │ │                 │
            └────────────────┘ └─────────────────┘ └─────────────────┘
```

## 🎯 High-Level System Decomposition

```
PAGEANTRY TABULATING SYSTEM
├── 🎨 Frontend Layer
│   ├── Admin Interface
│   ├── Judge Interface  
│   └── Public Interface
├── ⚙️ Backend Layer
│   ├── Authentication System
│   ├── Business Logic Modules
│   └── API Endpoints
├── 💾 Data Layer
│   ├── Database Management
│   ├── File Storage
│   └── Configuration Management
└── 🔧 Infrastructure Layer
    ├── Web Server (Apache)
    ├── Database Server (MySQL)
    └── File System
```

## 📊 Detailed Component Breakdown

### 🎨 PRESENTATION LAYER

```
Frontend Components
├── 👨‍💼 Admin Interface
│   ├── 📊 Dashboard
│   │   ├── System Statistics Widget
│   │   ├── Judge Progress Tracker
│   │   ├── Recent Activity Feed
│   │   └── Quick Actions Panel
│   ├── 👥 Candidate Management
│   │   ├── Candidate List View
│   │   ├── Add/Edit Candidate Form
│   │   ├── Photo Upload Component
│   │   ├── Candidate Number Assignment
│   │   └── Bulk Import Tool
│   ├── 📋 Criteria Management
│   │   ├── Criteria Definition Form
│   │   ├── Percentage Weight Setter
│   │   ├── Validation Rules Engine
│   │   └── Criteria Preview Panel
│   ├── ⚖️ Judge Management
│   │   ├── Judge Account Creator
│   │   ├── Judge List Manager
│   │   ├── Progress Monitor
│   │   └── Access Control Panel
│   ├── 🏆 Results & Analytics
│   │   ├── Live Results Dashboard
│   │   ├── Score Breakdown Tables
│   │   ├── Ranking Visualizer
│   │   ├── Winner Declaration Panel
│   │   └── Export Tools
│   └── ⚙️ System Settings
│       ├── Pageant Configuration
│       ├── System Preferences
│       └── Backup/Restore Tools
├── ⚖️ Judge Interface
│   ├── 📊 Judge Dashboard
│   │   ├── Personal Progress Tracker
│   │   ├── Assigned Candidates List
│   │   ├── Criteria Overview Panel
│   │   └── Submission Status Widget
│   ├── ⭐ Scoring Interface
│   │   ├── Candidate Selector
│   │   ├── Criteria-based Score Input
│   │   ├── Score Validation System
│   │   ├── Auto-save Mechanism
│   │   └── Score Review Panel
│   └── 👤 Profile Management
│       ├── Personal Information View
│       ├── Password Change Form
│       └── Scoring History Viewer
└── 🌐 Public Interface
    ├── 🏠 Homepage
    ├── 🔐 Login Portal
    └── 📱 Mobile-Responsive Design
```

### ⚙️ BUSINESS LOGIC LAYER

```
Core Business Modules
├── 🔐 Authentication Module
│   ├── 🔑 User Authentication
│   │   ├── Login Validator
│   │   ├── Password Hasher (bcrypt)
│   │   ├── Session Manager
│   │   └── CSRF Protection
│   ├── 👤 User Management
│   │   ├── User Registration
│   │   ├── Role Assignment (Admin/Judge)
│   │   ├── Account Activation
│   │   └── Password Reset
│   └── 🛡️ Security Layer
│       ├── Access Control Lists
│       ├── Session Hijacking Prevention
│       ├── Rate Limiting
│       └── Input Sanitization
├── 👥 Candidate Management Module
│   ├── 📝 CRUD Operations
│   │   ├── Create Candidate
│   │   ├── Read Candidate Data
│   │   ├── Update Candidate Info
│   │   └── Delete Candidate
│   ├── 📸 Photo Management
│   │   ├── File Upload Handler
│   │   ├── Image Validation (Type/Size)
│   │   ├── Image Resizing/Optimization
│   │   ├── Thumbnail Generator
│   │   └── File Storage Manager
│   ├── 🔢 Candidate Numbering
│   │   ├── Unique Number Generator
│   │   ├── Number Validation
│   │   └── Duplicate Prevention
│   └── ✅ Data Validation
│       ├── Required Field Checker
│       ├── Format Validator
│       ├── Age Range Validator
│       └── Data Sanitizer
├── 📋 Criteria Management Module
│   ├── 🎯 Criteria Definition
│   │   ├── Criteria Creator
│   │   ├── Description Manager
│   │   ├── Weight Assignment
│   │   └── Criteria Categorizer
│   ├── 📊 Percentage Management
│   │   ├── Weight Calculator
│   │   ├── 100% Validation Rule
│   │   ├── Percentage Distributor
│   │   └── Balance Checker
│   └── 🔍 Criteria Validation
│       ├── Duplicate Name Checker
│       ├── Weight Range Validator
│       ├── Total Percentage Validator
│       └── Criteria Dependency Checker
├── ⭐ Scoring Engine Module
│   ├── 📊 Score Collection
│   │   ├── Score Input Processor
│   │   ├── Range Validator (1-10)
│   │   ├── Duplicate Score Preventer
│   │   └── Auto-save Controller
│   ├── 🧮 Calculation Engine
│   │   ├── Average Score Calculator
│   │   ├── Weighted Score Processor
│   │   ├── Final Score Compiler
│   │   └── Ranking Algorithm
│   ├── ⚡ Real-time Processing
│   │   ├── Live Score Updater
│   │   ├── Progress Tracker
│   │   ├── Status Notifier
│   │   └── Change Broadcaster
│   └── 🔒 Data Integrity
│       ├── Score Audit Logger
│       ├── Modification Tracker
│       ├── Backup Score Manager
│       └── Recovery System
├── 🏆 Results Processing Module
│   ├── 📈 Results Calculator
│   │   ├── Final Score Aggregator
│   │   ├── Ranking Generator
│   │   ├── Tie-breaking Logic
│   │   └── Statistical Analyzer
│   ├── 🥇 Winner Declaration
│   │   ├── Winner Selector
│   │   ├── Award Category Assigner
│   │   ├── Certificate Generator
│   │   └── Announcement Formatter
│   └── 📊 Analytics Engine
│       ├── Score Distribution Analyzer
│       ├── Judge Performance Metrics
│       ├── Competition Statistics
│       └── Trend Analyzer
└── 📄 Reporting Module
    ├── 📋 Report Generator
    │   ├── PDF Report Creator
    │   ├── Excel Export Engine
    │   ├── CSV Data Exporter
    │   └── Print Layout Formatter
    ├── 📊 Analytics Dashboard
    │   ├── Score Visualization
    │   ├── Performance Charts
    │   ├── Progress Graphs
    │   └── Statistical Summaries
    └── 🖨️ Print Services
        ├── Certificate Printer
        ├── Score Sheet Generator
        ├── Winner Announcement
        └── Results Summary
```

### 💾 DATA LAYER

```
Data Management Components
├── 🗄️ Database Layer
│   ├── 📊 Core Tables
│   │   ├── users (Authentication & Roles)
│   │   │   ├── id (Primary Key)
│   │   │   ├── username (Unique)
│   │   │   ├── password_hash
│   │   │   ├── role (admin/judge)
│   │   │   ├── full_name
│   │   │   ├── email
│   │   │   ├── created_at
│   │   │   └── updated_at
│   │   ├── candidates (Contestant Data)
│   │   │   ├── id (Primary Key)
│   │   │   ├── candidate_number (Unique)
│   │   │   ├── name
│   │   │   ├── age
│   │   │   ├── hometown
│   │   │   ├── description
│   │   │   ├── photo_url
│   │   │   ├── created_at
│   │   │   └── updated_at
│   │   ├── criteria (Judging Standards)
│   │   │   ├── id (Primary Key)
│   │   │   ├── name
│   │   │   ├── description
│   │   │   ├── percentage
│   │   │   ├── created_at
│   │   │   └── updated_at
│   │   └── scores (Judge Evaluations)
│   │       ├── id (Primary Key)
│   │       ├── judge_id (Foreign Key → users.id)
│   │       ├── candidate_id (Foreign Key → candidates.id)
│   │       ├── criteria_id (Foreign Key → criteria.id)
│   │       ├── score (1-10 scale)
│   │       ├── created_at
│   │       └── updated_at
│   ├── 🔗 Relationships
│   │   ├── users ←→ scores (One-to-Many)
│   │   ├── candidates ←→ scores (One-to-Many)
│   │   ├── criteria ←→ scores (One-to-Many)
│   │   └── Composite Unique Key (judge_id, candidate_id, criteria_id)
│   └── 🔍 Indexes & Constraints
│       ├── Primary Key Indexes
│       ├── Foreign Key Constraints
│       ├── Unique Constraints
│       └── Performance Indexes
├── 📁 File Storage System
│   ├── 📷 Image Storage
│   │   ├── uploads/candidates/ (Candidate Photos)
│   │   ├── uploads/logos/ (System Logos)
│   │   └── cache/ (Temporary Files)
│   ├── 📄 Document Storage
│   │   ├── Generated Reports
│   │   ├── Exported Data
│   │   └── Backup Files
│   └── 🔒 File Security
│       ├── Access Control
│       ├── File Type Validation
│       ├── Size Limitations
│       └── Virus Scanning
└── ⚙️ Configuration Management
    ├── 🔧 System Configuration
    │   ├── config/database.php (DB Settings)
    │   ├── config/pageant_config.php (App Settings)
    │   └── config/settings.php (User Preferences)
    ├── 🌍 Environment Settings
    │   ├── Development Configuration
    │   ├── Production Configuration
    │   └── Testing Configuration
    └── 🔐 Security Configuration
        ├── Encryption Keys
        ├── Session Settings
        ├── CSRF Tokens
        └── Access Policies
```

## 🔄 System Data Flow

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    USER     │───▶│   INPUT     │───▶│ VALIDATION  │───▶│ PROCESSING  │
│ (Admin/     │    │ (Forms,     │    │ (Rules,     │    │ (Business   │
│  Judge)     │    │  Actions)   │    │  Checks)    │    │  Logic)     │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                                                                  │
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌───────▼─────┐
│   OUTPUT    │◀───│  RESPONSE   │◀───│   STORAGE   │◀───│ CALCULATION │
│ (Results,   │    │ (Pages,     │    │ (Database,  │    │ (Scoring,   │
│  Reports)   │    │  Data)      │    │  Files)     │    │  Rankings)  │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
```

## 🔗 Module Interaction Matrix

```
┌─────────────────┬─────┬─────┬─────┬─────┬─────┬─────┬─────┬─────┐
│     MODULE      │Auth │Cand │Crit │Judg │Scor │Resu │Repo │File │
├─────────────────┼─────┼─────┼─────┼─────┼─────┼─────┼─────┼─────┤
│ Authentication  │  -  │  ✓  │  ✓  │  ✓  │  ✓  │  ✓  │  ✓  │  ✓  │
│ Candidate Mgmt  │  ✓  │  -  │  ○  │  ○  │  ✓  │  ✓  │  ✓  │  ✓  │
│ Criteria Mgmt   │  ✓  │  ○  │  -  │  ○  │  ✓  │  ✓  │  ✓  │  ○  │
│ Judge Mgmt      │  ✓  │  ○  │  ○  │  -  │  ✓  │  ✓  │  ✓  │  ○  │
│ Scoring Engine  │  ✓  │  ✓  │  ✓  │  ✓  │  -  │  ✓  │  ✓  │  ○  │
│ Results Proc    │  ✓  │  ✓  │  ✓  │  ✓  │  ✓  │  -  │  ✓  │  ○  │
│ Reporting       │  ✓  │  ✓  │  ✓  │  ✓  │  ✓  │  ✓  │  -  │  ✓  │
│ File Management │  ○  │  ✓  │  ○  │  ○  │  ○  │  ○  │  ✓  │  -  │
└─────────────────┴─────┴─────┴─────┴─────┴─────┴─────┴─────┴─────┘

Legend: ✓ = Strong Dependency | ○ = Weak Dependency | - = Self
```

## 🏗️ Physical Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT TIER                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │
│  │   Desktop   │  │   Tablet    │  │   Mobile    │            │
│  │   Browser   │  │   Browser   │  │   Browser   │            │
│  └─────────────┘  └─────────────┘  └─────────────┘            │
└─────────────────────────────────────────────────────────────────┘
                              │ HTTP/HTTPS
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      WEB SERVER TIER                           │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Apache Web Server                       ││
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        ││
│  │  │     PHP     │  │   Static    │  │    File     │        ││
│  │  │  Processor  │  │   Assets    │  │   Uploads   │        ││
│  │  └─────────────┘  └─────────────┘  └─────────────┘        ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
                              │ SQL/TCP
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     DATABASE TIER                              │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    MySQL Database                          ││
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        ││
│  │  │    Tables   │  │   Indexes   │  │   Backups   │        ││
│  │  │   Storage   │  │ & Triggers  │  │ & Logs      │        ││
│  │  └─────────────┘  └─────────────┘  └─────────────┘        ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

## 🔄 Process Flow Diagrams

### Admin Workflow
```
Admin Login → Dashboard → Select Function
     │
     ├── Manage Candidates → Add/Edit/Delete → Update Database
     ├── Manage Criteria → Set Weights → Validate 100% → Save
     ├── Manage Judges → Create Accounts → Assign Roles
     └── View Results → Calculate Scores → Declare Winners
```

### Judge Workflow
```
Judge Login → Dashboard → View Progress
     │
     └── Score Candidates → Select Candidate → Rate Criteria → Save Scores
                                │
                                └── Auto-calculate → Update Rankings
```

### Scoring Process
```
Judge Submits Score → Validate Range (1-10) → Store in Database
     │
     └── Trigger Calculation → Average All Judge Scores → Apply Weights
                                     │
                                     └── Generate Rankings → Update Results
```

## 🎯 Key System Features

### 🔐 Security Features
- **Authentication**: Secure login with password hashing
- **Authorization**: Role-based access control (Admin/Judge)
- **Session Management**: Secure session handling with timeouts
- **Input Validation**: SQL injection and XSS prevention
- **CSRF Protection**: Cross-site request forgery prevention

### 📊 Scoring Features
- **Real-time Calculation**: Live score updates as judges submit
- **Weighted Scoring**: Automatic application of criteria percentages
- **Data Integrity**: Prevents duplicate scores and ensures completeness
- **Audit Trail**: Complete history of all score changes

### 🎨 User Experience Features
- **Responsive Design**: Works on all device sizes
- **Auto-save**: Automatic saving of scores to prevent data loss
- **Progress Tracking**: Visual indicators for completion status
- **Intuitive Interface**: User-friendly design for all user types

### 📈 Analytics Features
- **Real-time Results**: Live ranking updates
- **Detailed Breakdowns**: Score analysis by criteria and judge
- **Export Capabilities**: PDF, Excel, and CSV export options
- **Winner Declaration**: Automated winner selection with animations

This comprehensive decomposition diagram provides a complete view of the PageantrySystem architecture, showing how all components interact to create a robust pageant tabulating solution.
