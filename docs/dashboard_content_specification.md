# Pageant Tabulating System - Dashboard Content Specification

## Admin Dashboard Layout

### Header Section
```
┌─────────────────────────────────────────────────────────────────┐
│  🏆 Admin Dashboard - Pageantry Tabulating System              │
│  Welcome, [Admin Name] | Last Login: [Date/Time]               │
└─────────────────────────────────────────────────────────────────┘
```

### 1. System Status Overview (Top Row)
```
┌──────────────┬──────────────┬──────────────┬──────────────┐
│   👥 [##]    │   📋 [##]    │   ⚖️ [##]    │   ⭐ [####]  │
│ Candidates   │  Criteria    │   Judges     │   Scores     │
│              │              │              │  Submitted   │
└──────────────┴──────────────┴──────────────┴──────────────┘
```

**Content Details:**
- **Candidates**: Total number of registered contestants
- **Criteria**: Number of judging criteria defined
- **Judges**: Number of active judges in the system
- **Scores Submitted**: Total scores entered by all judges

### 2. Setup Status Alert (Conditional)
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚠️ SETUP REQUIRED                                               │
│ • Add candidates to the competition                             │
│ • Criteria percentages must total 100% (currently [X]%)        │
│ • Add judges to score the candidates                            │
└─────────────────────────────────────────────────────────────────┘
```

### 3. Quick Actions Panel
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚡ Quick Actions                                                │
│                                                                 │
│ [👥 Manage Candidates] [📋 Setup Criteria] [⚖️ Manage Judges]   │
│ [🏆 View Results] [⚙️ Pageant Settings]                         │
└─────────────────────────────────────────────────────────────────┘
```

### 4. Main Content Area (Two Columns)

#### Left Column: Judge Progress Tracking
```
┌─────────────────────────────────────────┐
│ 📊 Judge Progress                       │
│                                         │
│ Judge Name 1        [████████░░] 80%    │
│ 4 of 5 candidates scored               │
│                                         │
│ Judge Name 2        [██████░░░░] 60%    │
│ 3 of 5 candidates scored               │
│                                         │
│ Judge Name 3        [██████████] 100%   │
│ 5 of 5 candidates scored               │
└─────────────────────────────────────────┘
```

#### Right Column: Recent Activity Feed
```
┌─────────────────────────────────────────┐
│ 🕐 Recent Activity                      │
│                                         │
│ • Judge Smith scored Candidate A        │
│   2 minutes ago                         │
│                                         │
│ • Judge Johnson scored Candidate B      │
│   5 minutes ago                         │
│                                         │
│ • New candidate "Sarah Wilson" added    │
│   1 hour ago                            │
│                                         │
│ • Criteria "Evening Gown" updated       │
│   2 hours ago                           │
└─────────────────────────────────────────┘
```

### 5. Competition Status Summary
```
┌─────────────────────────────────────────────────────────────────┐
│ 🏁 Competition Status                                           │
│                                                                 │
│ Overall Progress: [████████░░] 85%                              │
│ • 4 of 5 judges have completed scoring                         │
│ • 23 of 25 total scores submitted                              │
│ • Ready for results: [Yes/No]                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Judge Dashboard Layout

### Header Section
```
┌─────────────────────────────────────────────────────────────────┐
│  ⚖️ Judge Dashboard - [Judge Name]                              │
│  Competition: [Pageant Name] | Role: Judge                     │
└─────────────────────────────────────────────────────────────────┘
```

### 1. Personal Progress Overview
```
┌─────────────────────────────────────────────────────────────────┐
│ 📈 Your Scoring Progress                                        │
│                                                                 │
│ Completed: [████████░░] 80% (4 of 5 candidates)                │
│ Remaining: 1 candidate to score                                │
│ Last Activity: 15 minutes ago                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 2. Quick Start Actions
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚡ Quick Actions                                                │
│                                                                 │
│ [⭐ Continue Scoring] [📊 View My Progress] [👤 Profile]        │
└─────────────────────────────────────────────────────────────────┘
```

### 3. Scoring Status Grid
```
┌─────────────────────────────────────────────────────────────────┐
│ 📋 Candidates & Scoring Status                                  │
│                                                                 │
│ Candidate A    [✅ Complete] [📝 Edit Scores]                   │
│ Candidate B    [✅ Complete] [📝 Edit Scores]                   │
│ Candidate C    [✅ Complete] [📝 Edit Scores]                   │
│ Candidate D    [✅ Complete] [📝 Edit Scores]                   │
│ Candidate E    [⏳ Pending]  [⭐ Score Now]                     │
└─────────────────────────────────────────────────────────────────┘
```

### 4. Criteria Reference Panel
```
┌─────────────────────────────────────────────────────────────────┐
│ 📏 Judging Criteria (Reference)                                │
│                                                                 │
│ • Beauty & Poise (30%)                                         │
│ • Talent Performance (25%)                                     │
│ • Evening Gown (20%)                                           │
│ • Interview Skills (15%)                                       │
│ • Swimwear (10%)                                               │
│                                                                 │
│ Scoring Scale: 1-10 (10 = Outstanding)                        │
└─────────────────────────────────────────────────────────────────┘
```

## Dashboard Features & Functionality

### Real-time Updates
- **Auto-refresh**: Dashboard updates every 30 seconds
- **Live notifications**: New scores, system changes
- **Progress indicators**: Visual progress bars with animations
- **Status badges**: Color-coded status indicators

### Interactive Elements
- **Clickable statistics**: Click numbers to view detailed breakdowns
- **Quick action buttons**: Direct navigation to key functions
- **Progress bars**: Clickable to show detailed progress
- **Activity feed**: Clickable items for more details

### Responsive Design Elements
```css
/* Mobile Layout Adjustments */
@media (max-width: 768px) {
  - Stack statistics vertically
  - Collapse quick actions into dropdown
  - Simplify progress displays
  - Optimize touch targets
}
```

### Color Coding System
- **🟢 Green**: Completed tasks, good status
- **🟡 Yellow**: In progress, warnings
- **🔴 Red**: Incomplete, errors, urgent
- **🔵 Blue**: Information, neutral status
- **🟣 Purple**: Special features, premium actions

### Data Refresh Intervals
- **Statistics**: Every 30 seconds
- **Progress bars**: Real-time on score submission
- **Activity feed**: Every 15 seconds
- **Status alerts**: Immediate on state change

### Accessibility Features
- **Screen reader support**: ARIA labels and descriptions
- **Keyboard navigation**: Tab-friendly interface
- **High contrast mode**: Alternative color schemes
- **Font scaling**: Responsive text sizing
- **Focus indicators**: Clear visual focus states
