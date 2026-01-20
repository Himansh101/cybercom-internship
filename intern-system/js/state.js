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
    
    // Initialize: Load users from memory
    init() {
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
            console.log('ℹ️ Default admin account created (username: admin, password: admin123)');
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
        const { username, password, fullname, role } = formData;

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
        console.log(`✅ New user registered: ${newUser.username} (${newUser.role})`);
        
        return newUser;
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

        // If logging in as INTERN, link to first ACTIVE intern
        let internId = null;
        if (user.role === 'INTERN') {
            const activeIntern = State.interns.find(i => i.status === 'ACTIVE');
            if (activeIntern) {
                internId = activeIntern.id;
            } else {
                throw new Error('No active intern account found. Please contact admin.');
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

        State.clearUser();

        document.getElementById('login-screen').style.display = 'flex';
        document.getElementById('main-app').style.display = 'none';

        console.log('✅ Logged out successfully');
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
    // Initialize Auth
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
                    role: e.target.role.value
                };

                if (!formData.username || !formData.password || !formData.fullname || !formData.role) {
                    throw new Error('All fields are required');
                }

                await Auth.signup(formData);

                errorDiv.innerHTML = '<div class="success">✅ Account created! Please login.</div>';
                e.target.reset();

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