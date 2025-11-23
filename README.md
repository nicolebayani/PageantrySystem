# Pageantry Tabulating System

A comprehensive web-based system for managing beauty pageant competitions, including candidate management, judging criteria, scoring, and automated winner declaration with animations.

## Features

### Admin Features
- **Candidate Management**: Add, edit, and remove contestants
- **Criteria Management**: Define scoring criteria with customizable percentages
- **Judge Management**: Create and manage judge accounts
- **Results & Analytics**: View real-time results with detailed breakdowns
- **Winner Declaration**: Animated winner announcement with confetti effects

### Judge Features
- **Secure Login**: Individual judge accounts with authentication
- **Scoring Interface**: User-friendly scoring system (1-10 scale)
- **Real-time Updates**: Automatic saving of scores
- **Progress Tracking**: Visual indicators for completion status

### System Features
- **Weighted Scoring**: Automatic calculation based on criteria percentages
- **Real-time Results**: Live updates as judges submit scores
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Data Validation**: Ensures scoring integrity and prevents errors

## Installation

### Prerequisites
- XAMPP (or similar PHP/MySQL environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Instructions

1. **Clone/Download** the project to your XAMPP htdocs directory:
   ```
   c:/xampp/htdocs/CascadeProjects/splitwise/
   ```

2. **Start XAMPP** services:
   - Apache
   - MySQL

3. **Create Database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `setup.sql` file to create the database and tables
   - Or run the SQL commands manually

4. **Configure Database** (if needed):
   - Edit `config/database.php` to match your MySQL settings
   - Default settings work with standard XAMPP installation

5. **Access the System**:
   - Navigate to: `http://localhost/CascadeProjects/splitwise/`

## Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`

### Sample Judge Account
- **Username**: `judge1`
- **Password**: `judge123`

## Usage Guide

### For Administrators

1. **Login** with admin credentials
2. **Add Candidates**: Go to Candidates section and add contestants
3. **Set Criteria**: Configure judging criteria and their percentages (must total 100%)
4. **Create Judges**: Add judge accounts for the competition
5. **Monitor Results**: View real-time results and declare winners

### For Judges

1. **Login** with provided judge credentials
2. **Score Candidates**: Rate each candidate (1-10) for each criteria
3. **Save Scores**: Scores are automatically saved and can be updated
4. **Complete Scoring**: Ensure all candidates are scored for all criteria

## Database Schema

### Tables
- `users`: Admin and judge accounts
- `candidates`: Contestant information
- `criteria`: Judging criteria with percentages
- `scores`: Individual judge scores for each candidate/criteria combination

### Key Relationships
- Scores link judges, candidates, and criteria
- Weighted calculations use criteria percentages
- Results aggregate all judge scores automatically

## Scoring System

### Scoring Scale
- **10**: Exceptional/Outstanding
- **8-9**: Very Good/Excellent
- **6-7**: Good/Above Average
- **4-5**: Average/Fair
- **1-3**: Below Average/Poor

### Calculation Method
1. Each judge scores candidates (1-10) for each criteria
2. Average scores calculated across all judges per criteria
3. Weighted scores computed using criteria percentages
4. Final scores ranked to determine winners

## Customization

### Adding New Features
- Extend database schema in `setup.sql`
- Add new PHP files following existing structure
- Update navigation in templates

### Styling
- Modify `assets/css/style.css` for visual changes
- Bootstrap 5 classes available throughout
- Custom animations in results page

### Security
- Change default passwords immediately
- Update `SECRET_KEY` in `app.py` for production
- Consider HTTPS for live deployment

## Troubleshooting

### Common Issues
1. **Database Connection**: Check XAMPP MySQL service and credentials
2. **File Permissions**: Ensure proper read/write permissions
3. **PHP Errors**: Enable error reporting for debugging
4. **Missing Scores**: Verify all judges have submitted complete scores

### Support
- Check browser console for JavaScript errors
- Review PHP error logs in XAMPP
- Ensure all required files are present

## File Structure
```
splitwise/
├── admin/              # Admin interface files
│   ├── candidates.php  # Candidate management
│   ├── criteria.php    # Criteria management
│   ├── judges.php      # Judge management
│   └── results.php     # Results and winner declaration
├── assets/
│   └── css/
│       └── style.css   # Custom styling
├── auth/               # Authentication
│   ├── login.php       # Login page
│   └── logout.php      # Logout handler
├── config/
│   └── database.php    # Database configuration
├── judge/
│   └── scoring.php     # Judge scoring interface
├── templates/
│   └── base.html       # Base template (legacy)
├── index.php           # Main homepage
├── setup.sql           # Database setup script
└── README.md           # This file
```

## License
This project is open source and available under the MIT License.
