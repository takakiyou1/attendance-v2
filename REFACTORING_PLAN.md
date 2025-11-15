# Attendance System v2 - MVC Refactoring Plan

## Current Issues
1. Mixed legacy files in root directory
2. Duplicate API endpoints (`api/` folder with inconsistent session handling)
3. No model layer - database queries scattered across controllers and views
4. Inline CSS/JS in views
5. No shared layout/template system
6. Test files in production code
7. Inconsistent session management

## New Architecture

```
attendance-v2/
├── app/
│   ├── Controllers/          # Business logic
│   │   ├── AuthController.php
│   │   ├── ShiftController.php
│   │   ├── UserController.php
│   │   ├── PayController.php
│   │   └── MenuController.php
│   │
│   ├── Models/               # Data layer
│   │   ├── BaseModel.php
│   │   ├── User.php
│   │   ├── Shift.php
│   │   ├── PaySetting.php
│   │   └── SpecialAllowance.php
│   │
│   ├── Views/                # Presentation layer
│   │   ├── layouts/
│   │   │   ├── main.php      # Main layout with header/footer
│   │   │   └── calendar.php  # Calendar-specific layout
│   │   ├── auth/
│   │   ├── shift/
│   │   ├── user/
│   │   ├── pay/
│   │   └── menu/
│   │
│   └── Helpers/              # Utility functions
│       ├── DateHelper.php
│       └── ValidationHelper.php
│
├── core/                     # Framework core
│   ├── Application.php       # Main application class
│   ├── Controller.php        # Base controller
│   ├── Model.php            # Base model with DB access
│   ├── View.php             # View renderer
│   ├── Router.php           # URL routing
│   ├── Database.php         # Database singleton
│   ├── Session.php          # Session management
│   └── Response.php         # HTTP response helper
│
├── config/                   # Configuration
│   ├── app.php              # Application config
│   ├── database.php         # Database config
│   └── session.php          # Session config
│
├── public/                   # Web-accessible directory
│   ├── index.php            # Entry point
│   ├── assets/
│   │   ├── css/
│   │   │   ├── main.css
│   │   │   └── calendar.css
│   │   ├── js/
│   │   │   ├── calendar.js
│   │   │   └── shift-manager.js
│   │   └── images/
│   └── .htaccess            # URL rewriting
│
├── routes/                   # Route definitions
│   └── web.php              # Web routes
│
├── legacy/                   # Backup of old files
│   ├── root_files/          # Old files from root
│   └── api/                 # Old API endpoints
│
├── docs/                     # Documentation
│   ├── ARCHITECTURE.md
│   ├── API.md
│   └── DEPLOYMENT.md
│
├── tests/                    # Test files (moved from root)
│   ├── test_data.php
│   ├── test_api.php
│   └── fix_shift_data.php
│
├── .gitignore
└── README.md
```

## Migration Steps

### Phase 1: Core Infrastructure
1. Create Database singleton
2. Create Session manager
3. Enhance Router with middleware support
4. Create Response helper
5. Create Application bootstrap

### Phase 2: Model Layer
1. BaseModel with CRUD operations
2. User model
3. Shift model
4. Pay-related models

### Phase 3: Controller Refactoring
1. Extract business logic from views
2. Use models instead of direct DB queries
3. Return proper responses
4. Add API methods to existing controllers

### Phase 4: View Organization
1. Create layout system
2. Extract inline CSS to files
3. Extract inline JS to files
4. Remove duplicate code

### Phase 5: Routes & APIs
1. Consolidate API endpoints into controllers
2. Define all routes in routes/web.php
3. Add API middleware for JSON responses

### Phase 6: Cleanup
1. Move legacy files
2. Remove duplicate code
3. Create documentation
4. Update .gitignore

## Implementation Priority
1. **Critical**: Core (Database, Session, Router)
2. **High**: Models (prevent SQL in views)
3. **High**: Controller refactoring
4. **Medium**: View layouts & assets
5. **Low**: Legacy cleanup & documentation

## Backward Compatibility
- Keep old API endpoints working during migration
- Test each feature after refactoring
- Gradual migration, not big-bang approach
