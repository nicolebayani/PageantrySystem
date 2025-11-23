# Pageant Tabulating System - Modules Specification

## Module Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    PAGEANT TABULATING SYSTEM                   │
├─────────────────────────────────────────────────────────────────┤
│  Presentation Layer (Frontend)                                 │
│  ├── Admin Interface                                           │
│  ├── Judge Interface                                           │
│  └── Public Interface                                          │
├─────────────────────────────────────────────────────────────────┤
│  Business Logic Layer (Backend)                                │
│  ├── Authentication Module                                     │
│  ├── Candidate Management Module                               │
│  ├── Criteria Management Module                                │
│  ├── Scoring Engine Module                                     │
│  ├── Results Processing Module                                 │
│  └── Reporting Module                                          │
├─────────────────────────────────────────────────────────────────┤
│  Data Access Layer                                             │
│  ├── Database Connection Module                                │
│  ├── Data Validation Module                                    │
│  └── File Management Module                                    │
├─────────────────────────────────────────────────────────────────┤
│  Database Layer                                                │
│  ├── Users Table                                               │
│  ├── Candidates Table                                          │
│  ├── Criteria Table                                            │
│  └── Scores Table                                              │
└─────────────────────────────────────────────────────────────────┘
```

## Core Modules

### 1. Authentication Module
**Location**: `auth/`
**Files**: `login.php`, `logout.php`, `register.php`

#### Purpose
Handles user authentication, session management, and access control.

#### Components
- **Login Handler**
  - Validates user credentials
  - Creates secure sessions
  - Implements role-based access
  
- **Registration System**
  - User account creation
  - Password encryption
  - Email validation
  
- **Session Manager**
  - Session security
  - Timeout handling
  - Role verification

#### Key Functions
```php
// Core authentication functions
authenticate_user($username, $password)
create_session($user_data)
check_admin_access()
check_judge_access()
logout_user()
```

#### Database Dependencies
- `users` table (id, username, password, role, full_name, email)

#### Security Features
- Password hashing (bcrypt)
- CSRF protection
- Session hijacking prevention
- Role-based access control

---

### 2. Candidate Management Module
**Location**: `admin/candidates.php`
**Dependencies**: Authentication Module, Database Module

#### Purpose
Manages contestant information, photos, and registration data.

#### Components
- **Candidate CRUD Operations**
  - Add new candidates
  - Edit candidate information
  - Delete candidates
  - View candidate profiles
  
- **Photo Management**
  - Upload candidate photos
  - Image validation and resizing
  - Photo gallery display
  
- **Data Validation**
  - Required field validation
  - Format checking
  - Duplicate prevention

#### Key Functions
```php
// Candidate management functions
add_candidate($data)
update_candidate($id, $data)
delete_candidate($id)
get_candidate_list()
upload_candidate_photo($candidate_id, $file)
```

#### Database Schema
```sql
candidates (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    age INT,
    hometown VARCHAR(255),
    photo_url VARCHAR(500),
    bio TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

---

### 3. Criteria Management Module
**Location**: `admin/criteria.php`
**Dependencies**: Authentication Module, Database Module

#### Purpose
Defines and manages judging criteria with weighted percentages.

#### Components
- **Criteria Definition**
  - Create judging categories
  - Set percentage weights
  - Define scoring guidelines
  
- **Validation System**
  - Ensure percentages total 100%
  - Prevent duplicate criteria
  - Validate weight ranges
  
- **Criteria Display**
  - Show criteria to judges
  - Display weights and descriptions
  - Provide scoring guidance

#### Key Functions
```php
// Criteria management functions
add_criteria($name, $percentage, $description)
update_criteria($id, $data)
delete_criteria($id)
validate_total_percentage()
get_criteria_list()
```

#### Database Schema
```sql
criteria (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    percentage DECIMAL(5,2),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

---

### 4. Scoring Engine Module
**Location**: `judge/scoring.php`, Core calculation functions
**Dependencies**: Authentication Module, Database Module

#### Purpose
Handles score collection, validation, and real-time calculations.

#### Components
- **Score Collection**
  - Judge scoring interface
  - Score validation (1-10 range)
  - Auto-save functionality
  
- **Calculation Engine**
  - Average score computation
  - Weighted score application
  - Real-time ranking updates
  
- **Data Integrity**
  - Duplicate score prevention
  - Score modification tracking
  - Audit trail maintenance

#### Key Functions
```php
// Scoring engine functions
submit_score($judge_id, $candidate_id, $criteria_id, $score)
calculate_candidate_average($candidate_id)
calculate_weighted_scores()
generate_rankings()
get_judge_progress($judge_id)
```

#### Database Schema
```sql
scores (
    id INT PRIMARY KEY,
    judge_id INT,
    candidate_id INT,
    criteria_id INT,
    score DECIMAL(3,1),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (judge_id) REFERENCES users(id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (criteria_id) REFERENCES criteria(id)
)
```

#### Calculation Logic
```
Final Score = Σ(Average Score per Criteria × Criteria Weight)

Where:
- Average Score = Σ(All Judge Scores for Criteria) / Number of Judges
- Criteria Weight = Percentage / 100
```

---

### 5. Results Processing Module
**Location**: `admin/results.php`
**Dependencies**: Scoring Engine, Database Module

#### Purpose
Processes final results, generates rankings, and handles winner declarations.

#### Components
- **Results Calculator**
  - Final score computation
  - Ranking generation
  - Tie-breaking logic
  
- **Winner Declaration**
  - Automated winner selection
  - Award category assignment
  - Certificate generation
  
- **Results Display**
  - Real-time results view
  - Detailed score breakdowns
  - Interactive charts and graphs

#### Key Functions
```php
// Results processing functions
calculate_final_results()
generate_final_rankings()
declare_winners()
get_detailed_scores($candidate_id)
export_results($format)
```

---

### 6. Reporting Module
**Location**: Various report generation files
**Dependencies**: Results Processing, Database Module

#### Purpose
Generates comprehensive reports and exports data in various formats.

#### Components
- **Report Generator**
  - PDF report creation
  - Excel export functionality
  - CSV data export
  
- **Analytics Dashboard**
  - Score distribution analysis
  - Judge performance metrics
  - Competition statistics
  
- **Print Functions**
  - Certificate printing
  - Score sheets
  - Winner announcements

#### Key Functions
```php
// Reporting functions
generate_pdf_report($type, $data)
export_to_excel($data)
create_certificates($winners)
get_competition_analytics()
print_score_sheets()
```

---

### 7. Database Connection Module
**Location**: `config/database.php`
**Purpose**: Centralized database connectivity and configuration

#### Components
- **Connection Manager**
  - Database connection establishment
  - Connection pooling
  - Error handling
  
- **Configuration**
  - Database credentials
  - Connection parameters
  - Environment settings

#### Key Functions
```php
// Database functions
class Database {
    public function getConnection()
    public function closeConnection()
    public function executeQuery($sql, $params)
    public function beginTransaction()
    public function commit()
    public function rollback()
}
```

---

## Module Relationships & Data Flow

### Inter-Module Communication
```
Authentication Module
    ↓ (User Session)
Admin/Judge Modules
    ↓ (User Actions)
Business Logic Modules
    ↓ (Data Operations)
Database Module
    ↓ (Data Storage)
Database Layer
```

### Data Flow Diagram
```
User Input → Validation → Processing → Storage → Output
    ↓           ↓           ↓          ↓        ↓
  Forms      Rules      Business    Database  Reports
  Scores     Checks     Logic       Tables    Results
  Files      Format     Calc        Backup    Exports
```

### Module Dependencies Matrix
```
Module                  | Auth | DB | Candidate | Criteria | Scoring | Results
------------------------|------|----|-----------|---------|---------|---------
Authentication          |  -   | ✓  |     -     |    -    |    -    |    -
Candidate Management    |  ✓   | ✓  |     -     |    -    |    -    |    -
Criteria Management     |  ✓   | ✓  |     -     |    -    |    -    |    -
Scoring Engine          |  ✓   | ✓  |     ✓     |    ✓    |    -    |    -
Results Processing      |  ✓   | ✓  |     ✓     |    ✓    |    ✓    |    -
Reporting               |  ✓   | ✓  |     ✓     |    ✓    |    ✓    |    ✓
```

## File Structure & Organization

```
PageantrySystem/
├── auth/                    # Authentication Module
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── admin/                   # Admin Interface Modules
│   ├── dashboard.php
│   ├── candidates.php
│   ├── criteria.php
│   ├── judges.php
│   ├── results.php
│   └── settings.php
├── judge/                   # Judge Interface Modules
│   ├── dashboard.php
│   └── scoring.php
├── config/                  # Configuration Modules
│   ├── database.php
│   ├── pageant_config.php
│   └── settings.php
├── includes/                # Shared Modules
│   ├── functions.php
│   ├── validation.php
│   └── helpers.php
├── assets/                  # Frontend Assets
│   ├── css/
│   ├── js/
│   └── images/
├── uploads/                 # File Upload Module
│   └── candidates/
├── docs/                    # Documentation
│   ├── functional_decomposition_diagram.md
│   ├── dashboard_content_specification.md
│   └── system_modules_specification.md
├── index.php               # Main Entry Point
├── setup.sql               # Database Schema
└── README.md               # System Documentation
```

## Module Integration Points

### 1. Authentication Integration
- All modules check user authentication
- Role-based access control throughout
- Session management across modules

### 2. Database Integration
- Centralized database connection
- Shared data models
- Transaction management

### 3. Validation Integration
- Consistent validation rules
- Cross-module data integrity
- Error handling standards

### 4. UI/UX Integration
- Consistent design patterns
- Shared CSS and JavaScript
- Responsive design across modules

This modular architecture ensures maintainability, scalability, and clear separation of concerns while providing a comprehensive pageant tabulating solution.
