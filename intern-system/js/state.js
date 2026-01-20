// state.js - Single source of truth + Authentication

// ===== AUTHENTICATION & AUTHORIZATION =====
const Auth = {
    // Available roles in the system
    ROLES: {
        ADMIN: 'ADMIN',
        MANAGER: 'MANAGER',
        INTERN: 'INTERN'
    },

    // User storage (simulates database)
    users: [],
    
    // LocalStorage keys
    STORAGE_KEYS: {
        USERS: 'intern_system_users',
        CURRENT_USER: 'intern_system_current_user'
    },
    
    // Initialize: Load users from localStorage
    init() {
        // Load users from localStorage
        const savedUsers = localStorage.getItem(this.STORAGE_KEYS.USERS);
        if (savedUsers) {
            try {
                this.users = JSON.parse(savedUsers);
                console.log(`ℹ️ Loaded ${this.users.length} users from storage`);
            } catch (error) {
                console.error('Error loading users from storage:', error);
                this.users = [];
            }
        }

        // Create default admin account if no users exist
        if (this.users.length === 0) {
            this.users.push({
                id: 'USER_ADMIN_DEFAULT',
                username: 'admin',
                password: 'admin123', // In production, this should be hashed!
                fullname: 'System Administrator',
                role: 'ADMIN',
                createdAt: new Date().toISOString()
            });
            this.saveUsers();
            console.log('ℹ️ Default admin account created (username: admin, password: admin123)');
        }

        // Check if user was previously logged in
        this.checkSavedSession();
    },

    // Save users to localStorage
    saveUsers() {
        try {
            localStorage.setItem(this.STORAGE_KEYS.USERS, JSON.stringify(this.users));
        } catch (error) {
            console.error('Error saving users to storage:', error);
        }
    },

    // Save current session
    saveSession(user) {
        try {
            localStorage.setItem(this.STORAGE_KEYS.CURRENT_USER, JSON.stringify(user));
        } catch (error) {
            console.error('Error saving session:', error);
        }
    },

    // Clear session
    clearSession() {
        try {
            localStorage.removeItem(this.STORAGE_KEYS.CURRENT_USER);
        } catch (error) {
            console.error('Error clearing session:', error);
        }
    },

    // Check for saved session on page load
    checkSavedSession() {
        const savedUser = localStorage.getItem(this.STORAGE_KEYS.CURRENT_USER);
        if (savedUser) {
            try {
                const user = JSON.parse(savedUser);
                console.log(`ℹ️ Found saved session for ${user.name}`);
                
                // Auto-login with saved session
                State.setUser(user);
                
                // Hide login screen, show main app
                document.getElementById('login-screen').style.display = 'none';
                document.getElementById('main-app').style.display = 'block';

                this.updateUserDisplay();
                this.renderNavigation();

                // Initialize the app
                if (typeof App !== 'undefined') {
                    App.wireFilters();
                    App.wireForms();
                }

                if (typeof Renderer !== 'undefined') {
                    Renderer.renderAll();
                }

                console.log('✅ Auto-logged in from saved session');
            } catch (error) {
                console.error('Error loading saved session:', error);
                this.clearSession();
            }
        }
    },

    // Role permissions configuration
    permissions: {
        ADMIN: {
            canCreateIntern: true,
            canActivateIntern: true,
            canExitIntern: true,
            canCreateTask: true,
            canAssignTask: true,
            canCompleteTask: true,
            canViewAllInterns: true,
            canViewAllTasks: true,
            canViewAllLogs: true,
            sections: ['dashboard', 'interns', 'tasks', 'logs']
        },
        MANAGER: {
            canCreateIntern: false,
            canActivateIntern: false,
            canExitIntern: false,
            canCreateTask: false,
            canAssignTask: true,
            canCompleteTask: false,
            canViewAllInterns: true,
            canViewAllTasks: true,
            canViewAllLogs: true,
            sections: ['dashboard', 'interns', 'tasks', 'logs']
        },
        INTERN: {
            canCreateIntern: false,
            canActivateIntern: false,
            canExitIntern: false,
            canCreateTask: false,
            canAssignTask: false,
            canCompleteTask: true,
            canViewAllInterns: false,
            canViewAllTasks: false,
            canViewAllLogs: false,
            sections: ['tasks']
        }
    },

    // Predefined demo users
    demoUsers: {
        ADMIN: {
            id: 'USER_ADMIN_001',
            name: 'Admin User',
            role: 'ADMIN',
            internId: null
        },
        MANAGER: {
            id: 'USER_MANAGER_001',
            name: 'Manager User',
            role: 'MANAGER',
            internId: null
        },
        INTERN: {
            id: 'USER_INTERN_001',
            name: 'Intern User',
            role: 'INTERN',
            internId: null
        }
    },

    // Show login form
    showLogin() {
        document.getElementById('login-form-container').style.display = 'block';
        document.getElementById('signup-form-container').style.display = 'none';
        document.getElementById('auth-subtitle').textContent = 'Please login to continue';
        document.getElementById('login-error').innerHTML = '';
        document.getElementById('signup-error').innerHTML = '';
    },

    // Show signup form
    showSignup() {
        document.getElementById('login-form-container').style.display = 'none';
        document.getElementById('signup-form-container').style.display = 'block';
        document.getElementById('auth-subtitle').textContent = 'Create your account';
        document.getElementById('login-error').innerHTML = '';
        document.getElementById('signup-error').innerHTML = '';
    },

    // Signup new user
    async signup(formData) {
        const { username, password, fullname, role, skills } = formData;

        // Validate username
        if (username.length < 3 || username.length > 20) {
            throw new Error('Username must be 3-20 characters');
        }

        // Validate password
        if (password.length < 6) {
            throw new Error('Password must be at least 6 characters');
        }

        // Check if username already exists
        if (this.users.find(u => u.username.toLowerCase() === username.toLowerCase())) {
            throw new Error('Username already exists');
        }

        // Validate role
        if (!this.ROLES[role]) {
            throw new Error('Invalid role selected');
        }

        // Validate skills for INTERN role
        if (role === 'INTERN') {
            if (!skills || skills.trim() === '') {
                throw new Error('Skills are required for intern registration');
            }
        }

        // Simulate async operation
        await new Promise(resolve => setTimeout(resolve, 500));

        // Create new user
        const newUser = {
            id: `USER_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            username: username.toLowerCase(),
            password: password, // In production, hash this!
            fullname: fullname.trim(),
            role: role,
            createdAt: new Date().toISOString()
        };

        this.users.push(newUser);
        this.saveUsers(); // Save to localStorage

        // If user is registering as INTERN, automatically create intern record
        if (role === 'INTERN') {
            const skillsArray = skills.split(',').map(s => s.trim()).filter(s => s);
            const internRecord = await this.createInternRecordForUser(newUser, skillsArray);
            newUser.internId = internRecord.id; // Link user to intern record
            this.saveUsers(); // Update user with internId
        }

        console.log(`✅ New user registered: ${newUser.username} (${newUser.role})`);
        
        return newUser;
    },

    // Create intern record when user signs up as INTERN
    async createInternRecordForUser(user, skills = []) {
        // Generate unique email from username
        const email = `${user.username}@intern.system`;

        // Check email uniqueness
        if (State.usedEmails.has(email.toLowerCase())) {
            // If email exists, add random suffix
            const randomEmail = `${user.username}_${Date.now()}@intern.system`;
            State.usedEmails.add(randomEmail.toLowerCase());
            
            const intern = {
                id: `${State.currentYear}_${String(State.nextInternId).padStart(3, '0')}`,
                name: user.fullname,
                email: randomEmail,
                skills: skills, // Skills provided during signup
                status: 'ONBOARDING',
                createdAt: new Date().toISOString(),
                assignedTasks: [],
                userId: user.id // Link back to user account
            };

            State.nextInternId++;
            State.interns.push(intern);
            State.addLog('INTERN_CREATED', `${intern.name} (${intern.id}) registered with skills: ${skills.join(', ')}`);
            State.saveState(); // Save to localStorage
            
            return intern;
        }

        State.usedEmails.add(email.toLowerCase());

        const intern = {
            id: `${State.currentYear}_${String(State.nextInternId).padStart(3, '0')}`,
            name: user.fullname,
            email: email,
            skills: skills, // Skills provided during signup
            status: 'ONBOARDING',
            createdAt: new Date().toISOString(),
            assignedTasks: [],
            userId: user.id // Link back to user account
        };

        State.nextInternId++;
        State.interns.push(intern);
        State.addLog('INTERN_CREATED', `${intern.name} (${intern.id}) registered with skills: ${skills.join(', ')}`);
        State.saveState(); // Save to localStorage
        
        return intern;
    },

    // Login with username and password
    async loginWithCredentials(username, password) {
        // Simulate async operation
        await new Promise(resolve => setTimeout(resolve, 800));

        // Find user
        const user = this.users.find(u => 
            u.username.toLowerCase() === username.toLowerCase()
        );

        if (!user) {
            throw new Error('Invalid username or password');
        }

        if (user.password !== password) {
            throw new Error('Invalid username or password');
        }

        // Get internId based on role
        let internId = null;
        if (user.role === 'INTERN') {
            // Find intern record linked to this user
            const linkedIntern = State.interns.find(i => i.userId === user.id);
            
            if (linkedIntern) {
                internId = linkedIntern.id;
                
                // Check if intern is activated
                if (linkedIntern.status === 'ONBOARDING') {
                    throw new Error('Your account is pending approval. Please wait for admin to activate your account.');
                }
                
                if (linkedIntern.status === 'EXITED') {
                    throw new Error('Your intern account has been deactivated. Please contact admin.');
                }
            } else {
                throw new Error('No intern record found. Please contact admin.');
            }
        }

        // Create session user
        const sessionUser = {
            id: user.id,
            name: user.fullname,
            role: user.role,
            internId: internId
        };

        State.setUser(sessionUser);
        this.saveSession(sessionUser); // Save session to localStorage

        // Hide login screen, show main app
        document.getElementById('login-screen').style.display = 'none';
        document.getElementById('main-app').style.display = 'block';

        this.updateUserDisplay();
        this.renderNavigation();

        // Initialize the app after login
        if (typeof App !== 'undefined') {
            App.wireFilters();
            App.wireForms();
        }

        if (typeof Renderer !== 'undefined') {
            Renderer.renderAll();
        }

        console.log(`✅ Logged in as ${sessionUser.name} (${sessionUser.role})`);
        return sessionUser;
    },

    // Logout current user
    logout() {
        if (!State.currentUser) return;

        // Ask for confirmation before logout
        if (!confirm('Are you sure you want to logout?')) {
            return; // User clicked "Cancel", don't logout
        }

        const userName = State.currentUser.name;
        
        State.clearUser();
        this.clearSession(); // Clear from localStorage

        document.getElementById('login-screen').style.display = 'flex';
        document.getElementById('main-app').style.display = 'none';

        console.log(`✅ ${userName} logged out successfully`);
    },

    // Update user display in header
    updateUserDisplay() {
        const nameEl = document.getElementById('current-user-name');
        const roleEl = document.getElementById('current-user-role');

        if (State.currentUser) {
            nameEl.textContent = State.currentUser.name;
            roleEl.textContent = State.currentUser.role;
        }
    },

    // Render navigation based on role permissions
    renderNavigation() {
        const nav = document.getElementById('main-nav');
        if (!nav || !State.currentUser) return;

        const allowedSections = this.permissions[State.currentUser.role].sections;

        const navButtons = {
            'dashboard': { icon: '📊', label: 'Dashboard' },
            'interns': { icon: '👥', label: 'Interns' },
            'tasks': { icon: '📋', label: 'Tasks' },
            'logs': { icon: '📜', label: 'Activity Logs' }
        };

        // Determine default active section based on role
        const defaultSection = allowedSections[0]; // First allowed section

        nav.innerHTML = allowedSections.map(section => `
            <button class="nav-btn ${section === defaultSection ? 'active' : ''}" 
                    data-view="${section}">
                ${navButtons[section].icon} ${navButtons[section].label}
            </button>
        `).join('');

        // Show the default section and hide others
        document.querySelectorAll('.view').forEach(view => {
            view.classList.remove('active');
        });
        document.getElementById(defaultSection).classList.add('active');
        State.currentView = defaultSection;

        if (typeof App !== 'undefined' && App.wireNavigation) {
            App.wireNavigation();
        }
    },

    // Check if current user has specific permission
    can(permission) {
        if (!State.currentUser) return false;
        return this.permissions[State.currentUser.role][permission] || false;
    },

    // Check if current user can view specific section
    canViewSection(section) {
        if (!State.currentUser) return false;
        return this.permissions[State.currentUser.role].sections.includes(section);
    },

    getRole() {
        return State.currentUser?.role || null;
    },

    isAuthenticated() {
        return State.isAuthenticated;
    },

    getCurrentUser() {
        return State.currentUser;
    },

    isAdmin() {
        return State.currentUser?.role === 'ADMIN';
    },

    isManager() {
        return State.currentUser?.role === 'MANAGER';
    },

    isIntern() {
        return State.currentUser?.role === 'INTERN';
    },

    getPermissions() {
        if (!State.currentUser) return null;
        return this.permissions[State.currentUser.role];
    }
};

// Wire up login/signup forms
document.addEventListener('DOMContentLoaded', () => {
    // Initialize State FIRST
    State.initializeState();
    
    // Then Initialize Auth
    Auth.init();

    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('login-error');
            errorDiv.innerHTML = '';

            try {
                errorDiv.innerHTML = '<div class="loading">⏳ Logging in...</div>';

                const username = e.target.username.value.trim();
                const password = e.target.password.value;

                if (!username || !password) {
                    throw new Error('Please enter username and password');
                }

                await Auth.loginWithCredentials(username, password);

                errorDiv.innerHTML = '<div class="success">✅ Login successful!</div>';
                e.target.reset();
            } catch (error) {
                errorDiv.innerHTML = `<div class="error">❌ ${error.message}</div>`;
            }
        });
    }

    // Signup form
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('signup-error');
            errorDiv.innerHTML = '';

            try {
                errorDiv.innerHTML = '<div class="loading">⏳ Creating account...</div>';

                const formData = {
                    username: e.target.username.value.trim(),
                    password: e.target.password.value,
                    fullname: e.target.fullname.value.trim(),
                    role: e.target.role.value,
                    skills: e.target.skills ? e.target.skills.value.trim() : ''
                };

                if (!formData.username || !formData.password || !formData.fullname || !formData.role) {
                    throw new Error('All required fields must be filled');
                }

                await Auth.signup(formData);

                errorDiv.innerHTML = '<div class="success">✅ Account created! Please login.</div>';
                e.target.reset();
                
                // Hide skills field after reset
                const skillsGroup = document.getElementById('skills-group');
                if (skillsGroup) skillsGroup.style.display = 'none';

                // Switch to login form after 2 seconds
                setTimeout(() => {
                    Auth.showLogin();
                }, 2000);
            } catch (error) {
                errorDiv.innerHTML = `<div class="error">❌ ${error.message}</div>`;
            }
        });
    }
});

// ===== STATE MANAGEMENT =====
const State = {
    // User Authentication
    currentUser: null,
    isAuthenticated: false,

    // Core Data
    interns: [],
    tasks: [],
    logs: [],
    usedEmails: new Set(),
    
    // UI State
    currentView: 'dashboard',
    
    // ID Generators
    nextInternId: 1,
    nextTaskId: 1,
    currentYear: new Date().getFullYear(),

    // LocalStorage keys
    STORAGE_KEYS: {
        INTERNS: 'intern_system_interns',
        TASKS: 'intern_system_tasks',
        LOGS: 'intern_system_logs',
        USED_EMAILS: 'intern_system_used_emails',
        NEXT_INTERN_ID: 'intern_system_next_intern_id',
        NEXT_TASK_ID: 'intern_system_next_task_id'
    },

    // Initialize state from localStorage
    initializeState() {
        try {
            // Load interns
            const savedInterns = localStorage.getItem(this.STORAGE_KEYS.INTERNS);
            if (savedInterns) {
                this.interns = JSON.parse(savedInterns);
                console.log(`✅ Loaded ${this.interns.length} interns from storage`);
                // Debug: Show intern statuses
                this.interns.forEach(intern => {
                    console.log(`   - ${intern.name}: ${intern.status}`);
                });
            }

            // Load tasks
            const savedTasks = localStorage.getItem(this.STORAGE_KEYS.TASKS);
            if (savedTasks) {
                this.tasks = JSON.parse(savedTasks);
                console.log(`✅ Loaded ${this.tasks.length} tasks from storage`);
            }

            // Load logs
            const savedLogs = localStorage.getItem(this.STORAGE_KEYS.LOGS);
            if (savedLogs) {
                this.logs = JSON.parse(savedLogs);
                console.log(`✅ Loaded ${this.logs.length} logs from storage`);
            }

            // Load used emails
            const savedEmails = localStorage.getItem(this.STORAGE_KEYS.USED_EMAILS);
            if (savedEmails) {
                this.usedEmails = new Set(JSON.parse(savedEmails));
                console.log(`✅ Loaded ${this.usedEmails.size} used emails from storage`);
            }

            // Load ID counters
            const savedInternId = localStorage.getItem(this.STORAGE_KEYS.NEXT_INTERN_ID);
            if (savedInternId) {
                this.nextInternId = parseInt(savedInternId);
            }

            const savedTaskId = localStorage.getItem(this.STORAGE_KEYS.NEXT_TASK_ID);
            if (savedTaskId) {
                this.nextTaskId = parseInt(savedTaskId);
            }

            console.log(`✅ State initialization complete`);
        } catch (error) {
            console.error('Error loading state from localStorage:', error);
        }
    },

    // Save all state to localStorage
    saveState() {
        try {
            localStorage.setItem(this.STORAGE_KEYS.INTERNS, JSON.stringify(this.interns));
            localStorage.setItem(this.STORAGE_KEYS.TASKS, JSON.stringify(this.tasks));
            localStorage.setItem(this.STORAGE_KEYS.LOGS, JSON.stringify(this.logs));
            localStorage.setItem(this.STORAGE_KEYS.USED_EMAILS, JSON.stringify(Array.from(this.usedEmails)));
            localStorage.setItem(this.STORAGE_KEYS.NEXT_INTERN_ID, this.nextInternId.toString());
            localStorage.setItem(this.STORAGE_KEYS.NEXT_TASK_ID, this.nextTaskId.toString());
            
            console.log(`💾 State saved: ${this.interns.length} interns, ${this.tasks.length} tasks`);
        } catch (error) {
            console.error('Error saving state to localStorage:', error);
        }
    },

    // Update state and trigger re-render
    update(updates) {
        Object.assign(this, updates);
        if (this.isAuthenticated && typeof Renderer !== 'undefined') {
            Renderer.renderAll();
        }
    },

    // Add activity log
    addLog(action, details) {
        this.logs.unshift({
            timestamp: new Date().toISOString(),
            action,
            details,
            userId: this.currentUser?.id || 'SYSTEM',
            userRole: this.currentUser?.role || 'SYSTEM'
        });
        
        if (this.logs.length > 100) {
            this.logs = this.logs.slice(0, 100);
        }
        
        if (this.isAuthenticated && typeof Renderer !== 'undefined') {
            Renderer.renderLogs();
            Renderer.renderStats();
        }
    },

    // Set current user (called by Auth.login)
    setUser(user) {
        this.currentUser = user;
        this.isAuthenticated = true;
        this.addLog('USER_LOGIN', `${user.name} logged in as ${user.role}`);
    },

    // Clear current user (called by Auth.logout)
    clearUser() {
        if (this.currentUser) {
            this.addLog('USER_LOGOUT', `${this.currentUser.name} logged out`);
        }
        this.currentUser = null;
        this.isAuthenticated = false;
        this.currentView = 'dashboard'; // Reset to default view
    },

    // Get filtered data based on user role
    getVisibleInterns() {
        if (!this.currentUser) return [];
        return this.interns;
    },

    getVisibleTasks() {
        if (!this.currentUser) return [];
        
        // INTERN role: only see tasks assigned to them specifically
        if (this.currentUser.role === 'INTERN' && this.currentUser.internId) {
            // Filter tasks where assignedTo matches the intern's ID
            return this.tasks.filter(t => t.assignedTo === this.currentUser.internId);
        }
        
        // ADMIN and MANAGER: see all tasks
        return this.tasks;
    },

    getVisibleLogs() {
        if (!this.currentUser) return [];
        
        // INTERN role: only see logs related to their own tasks
        if (this.currentUser.role === 'INTERN' && this.currentUser.internId) {
            return this.logs.filter(log => {
                // Show logs where:
                // 1. They performed the action themselves
                // 2. The log mentions their intern ID
                // 3. The log mentions a task assigned to them
                if (log.userId === this.currentUser.id) return true;
                if (log.details.includes(this.currentUser.internId)) return true;
                
                // Check if any of their assigned tasks are mentioned
                const myTasks = this.tasks.filter(t => t.assignedTo === this.currentUser.internId);
                return myTasks.some(task => log.details.includes(task.id));
            });
        }
        
        // ADMIN and MANAGER: see all logs
        return this.logs;
    },

    // Reset all data
    reset() {
        this.interns = [];
        this.tasks = [];
        this.logs = [];
        this.usedEmails = new Set();
        this.nextInternId = 1;
        this.nextTaskId = 1;
        this.currentView = 'dashboard';
        
        if (this.isAuthenticated && typeof Renderer !== 'undefined') {
            Renderer.renderAll();
        }
    }
};