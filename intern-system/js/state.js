// const State = {
//     interns: [],
//     tasks: [],
//     logs: [],
//     usedEmails: new Set(),
//     currentView: 'dashboard',
//     nextInternId: 1,
//     nextTaskId: 1,
//     currentYear: new Date().getFullYear(),

//     update(updates) {
//         Object.assign(this, updates);
//         Renderer.renderAll();
//     },

//     addLog(action, details) {
//         this.logs.unshift({
//             timestamp: new Date().toISOString(),
//             action,
//             details
//         });
//         if (this.logs.length > 100) {
//             this.logs = this.logs.slice(0, 100);
//         }
//         Renderer.renderLogs();
//         Renderer.renderStats();
//     }
// };

// state.js - Single source of truth
// const State = {
//     // User Authentication
//     currentUser: null, // Will be set after login: { id, name, role, internId }
//     isAuthenticated: false,

//     // Core Data
//     interns: [],
//     tasks: [],
//     logs: [],
//     usedEmails: new Set(),
    
//     // UI State
//     currentView: 'dashboard',
    
//     // ID Generators
//     nextInternId: 1,
//     nextTaskId: 1,
//     currentYear: new Date().getFullYear(),

//     // Update state and trigger re-render
//     update(updates) {
//         Object.assign(this, updates);
//         if (this.isAuthenticated && typeof Renderer !== 'undefined') {
//             Renderer.renderAll();
//         }
//     },

//     // Add activity log
//     addLog(action, details) {
//         this.logs.unshift({
//             timestamp: new Date().toISOString(),
//             action,
//             details,
//             userId: this.currentUser?.id || 'SYSTEM',
//             userRole: this.currentUser?.role || 'SYSTEM'
//         });
        
//         // Keep only last 100 logs
//         if (this.logs.length > 100) {
//             this.logs = this.logs.slice(0, 100);
//         }
        
//         if (this.isAuthenticated && typeof Renderer !== 'undefined') {
//             Renderer.renderLogs();
//             Renderer.renderStats();
//         }
//     },

//     // Set current user (called by Auth.login)
//     setUser(user) {
//         this.currentUser = user;
//         this.isAuthenticated = true;
//         this.addLog('USER_LOGIN', `${user.name} logged in as ${user.role}`);
//     },

//     // Clear current user (called by Auth.logout)
//     clearUser() {
//         if (this.currentUser) {
//             this.addLog('USER_LOGOUT', `${this.currentUser.name} logged out`);
//         }
//         this.currentUser = null;
//         this.isAuthenticated = false;
//         this.currentView = 'dashboard';
//     },

//     // Get filtered data based on user role
//     getVisibleInterns() {
//         if (!this.currentUser) return [];
        
//         // All roles can see all interns (different from tasks)
//         return this.interns;
//     },

//     getVisibleTasks() {
//         if (!this.currentUser) return [];
        
//         // INTERN role: only see their assigned tasks
//         if (this.currentUser.role === 'INTERN' && this.currentUser.internId) {
//             const intern = this.interns.find(i => i.id === this.currentUser.internId);
//             if (intern) {
//                 return this.tasks.filter(t => intern.assignedTasks.includes(t.id));
//             }
//             return [];
//         }
        
//         // ADMIN and MANAGER: see all tasks
//         return this.tasks;
//     },

//     getVisibleLogs() {
//         if (!this.currentUser) return [];
        
//         // INTERN role: only see logs related to their tasks
//         if (this.currentUser.role === 'INTERN' && this.currentUser.internId) {
//             const intern = this.interns.find(i => i.id === this.currentUser.internId);
//             if (intern) {
//                 return this.logs.filter(log => {
//                     // Show logs about their tasks or their own actions
//                     return log.userId === this.currentUser.id || 
//                            intern.assignedTasks.some(taskId => log.details.includes(taskId));
//                 });
//             }
//             return [];
//         }
        
//         // ADMIN and MANAGER: see all logs
//         return this.logs;
//     },

//     // Reset all data (useful for testing)
//     reset() {
//         this.interns = [];
//         this.tasks = [];
//         this.logs = [];
//         this.usedEmails = new Set();
//         this.nextInternId = 1;
//         this.nextTaskId = 1;
//         this.currentView = 'dashboard';
        
//         if (this.isAuthenticated && typeof Renderer !== 'undefined') {
//             Renderer.renderAll();
//         }
//     }
// };

// state.js - Single source of truth + Authentication

// ===== AUTHENTICATION & AUTHORIZATION =====
const Auth = {
    // Available roles in the system
    ROLES: {
        ADMIN: 'ADMIN',
        MANAGER: 'MANAGER',
        INTERN: 'INTERN'
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

    // Login user with specified role
    login(role) {
        if (!this.ROLES[role]) {
            console.error('Invalid role:', role);
            return;
        }

        const user = { ...this.demoUsers[role] };

        // If logging in as INTERN, link to first ACTIVE intern
        if (role === 'INTERN') {
            const activeIntern = State.interns.find(i => i.status === 'ACTIVE');
            if (activeIntern) {
                user.internId = activeIntern.id;
                user.name = activeIntern.name;
            } else {
                alert('⚠️ No active interns found. Please login as Admin first to create and activate an intern.');
                return;
            }
        }

        State.setUser(user);

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

        console.log(`✅ Logged in as ${user.name} (${role})`);
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
        
        if (this.currentUser.role === 'INTERN' && this.currentUser.internId) {
            const intern = this.interns.find(i => i.id === this.currentUser.internId);
            if (intern) {
                return this.tasks.filter(t => intern.assignedTasks.includes(t.id));
            }
            return [];
        }
        
        return this.tasks;
    },

    getVisibleLogs() {
        if (!this.currentUser) return [];
        
        if (this.currentUser.role === 'INTERN' && this.currentUser.internId) {
            const intern = this.interns.find(i => i.id === this.currentUser.internId);
            if (intern) {
                return this.logs.filter(log => {
                    return log.userId === this.currentUser.id || 
                           intern.assignedTasks.some(taskId => log.details.includes(taskId));
                });
            }
            return [];
        }
        
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