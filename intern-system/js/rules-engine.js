// const RulesEngine = {
//     canAssignTask(task, intern) {
//         if (intern.status !== 'ACTIVE') {
//             return {
//                 allowed: false,
//                 reason: 'Only ACTIVE interns can receive tasks'
//             };
//         }

//         const hasRequiredSkills = task.requiredSkills.every(skill =>
//             intern.skills.some(s => s.toLowerCase() === skill.toLowerCase())
//         );

//         if (!hasRequiredSkills) {
//             return {
//                 allowed: false,
//                 reason: 'Intern does not have required skills'
//             };
//         }

//         if (intern.assignedTasks.includes(task.id)) {
//             return {
//                 allowed: false,
//                 reason: 'Task already assigned to this intern'
//             };
//         }

//         if (task.assignedTo) {
//             return {
//                 allowed: false,
//                 reason: 'Task already assigned to another intern'
//             };
//         }

//         if (task.dependencies && task.dependencies.length > 0) {
//             const unresolvedDeps = task.dependencies.filter(depId => {
//                 const depTask = State.tasks.find(t => t.id === depId);
//                 return !depTask || depTask.status !== 'DONE';
//             });

//             if (unresolvedDeps.length > 0) {
//                 return {
//                     allowed: false,
//                     reason: `Dependencies not resolved: ${unresolvedDeps.join(', ')}`
//                 };
//             }
//         }

//         return { allowed: true };
//     },

//     canTransitionStatus(intern, newStatus) {
//         return Validators.validateStatusTransition(intern.status, newStatus);
//     },

//     calculateTotalHours(task, visited = new Set()) {
//         if (visited.has(task.id)) {
//             return 0;
//         }

//         visited.add(task.id);

//         if (!task.dependencies || task.dependencies.length === 0) {
//             return task.estimatedHours;
//         }

//         let total = task.estimatedHours;
//         for (const depId of task.dependencies) {
//             const depTask = State.tasks.find(t => t.id === depId);
//             if (depTask) {
//                 total += this.calculateTotalHours(depTask, visited);
//             }
//         }

//         return total;
//     },

//     autoUpdateTaskStatus(taskId) {
//         const task = State.tasks.find(t => t.id === taskId);
//         if (!task || !task.dependencies || task.dependencies.length === 0) {
//             return;
//         }

//         const allDependenciesResolved = task.dependencies.every(depId => {
//             const depTask = State.tasks.find(t => t.id === depId);
//             return depTask && depTask.status === 'DONE';
//         });

//         if (allDependenciesResolved && task.status === 'BLOCKED') {
//             task.status = 'PENDING';
//             State.addLog('TASK_UNBLOCKED', `Task "${task.title}" is now available for assignment`);
//             Renderer.renderTasks();
//         } else if (!allDependenciesResolved && task.status === 'PENDING' && !task.assignedTo) {
//             task.status = 'BLOCKED';
//             State.addLog('TASK_BLOCKED', `Task "${task.title}" blocked by dependencies`);
//             Renderer.renderTasks();
//         }
//     }
// };

// rules-engine.js - Business rules with role-based permissions

const RulesEngine = {
    // Check if a task can be assigned to an intern
    canAssignTask(task, intern) {
        // First check user permissions
        if (!Auth.can('canAssignTask')) {
            return {
                allowed: false,
                reason: 'You do not have permission to assign tasks'
            };
        }

        if (intern.status !== 'ACTIVE') {
            return {
                allowed: false,
                reason: 'Only ACTIVE interns can receive tasks'
            };
        }

        const hasRequiredSkills = task.requiredSkills.every(skill =>
            intern.skills.some(s => s.toLowerCase() === skill.toLowerCase())
        );

        if (!hasRequiredSkills) {
            return {
                allowed: false,
                reason: 'Intern does not have required skills'
            };
        }

        if (intern.assignedTasks.includes(task.id)) {
            return {
                allowed: false,
                reason: 'Task already assigned to this intern'
            };
        }

        if (task.assignedTo) {
            return {
                allowed: false,
                reason: 'Task already assigned to another intern'
            };
        }

        if (task.dependencies && task.dependencies.length > 0) {
            const unresolvedDeps = task.dependencies.filter(depId => {
                const depTask = State.tasks.find(t => t.id === depId);
                return !depTask || depTask.status !== 'DONE';
            });

            if (unresolvedDeps.length > 0) {
                return {
                    allowed: false,
                    reason: `Dependencies not resolved: ${unresolvedDeps.join(', ')}`
                };
            }
        }

        return { allowed: true };
    },

    // Check if user can create an intern
    canCreateIntern() {
        return Auth.can('canCreateIntern');
    },

    // Check if user can activate an intern
    canActivateIntern() {
        return Auth.can('canActivateIntern');
    },

    // Check if user can exit an intern
    canExitIntern() {
        return Auth.can('canExitIntern');
    },

    // Check if user can create a task
    canCreateTask() {
        return Auth.can('canCreateTask');
    },

    // Check if user can complete a task
    canCompleteTask(task) {
        if (!Auth.can('canCompleteTask')) {
            return {
                allowed: false,
                reason: 'You do not have permission to complete tasks'
            };
        }

        // If user is INTERN, they can only complete their own tasks
        if (Auth.isIntern()) {
            const currentInternId = State.currentUser.internId;
            if (task.assignedTo !== currentInternId) {
                return {
                    allowed: false,
                    reason: 'You can only complete tasks assigned to you'
                };
            }
        }

        if (task.status !== 'IN_PROGRESS') {
            return {
                allowed: false,
                reason: 'Only IN_PROGRESS tasks can be completed'
            };
        }

        return { allowed: true };
    },

    // Check if intern status can transition
    canTransitionStatus(intern, newStatus) {
        // Check user permission first
        if (newStatus === 'ACTIVE' && !Auth.can('canActivateIntern')) {
            return false;
        }

        if (newStatus === 'EXITED' && !Auth.can('canExitIntern')) {
            return false;
        }

        return Validators.validateStatusTransition(intern.status, newStatus);
    },

    // Calculate total hours including dependencies (recursive)
    calculateTotalHours(task, visited = new Set()) {
        if (visited.has(task.id)) {
            return 0;
        }

        visited.add(task.id);

        if (!task.dependencies || task.dependencies.length === 0) {
            return task.estimatedHours;
        }

        let total = task.estimatedHours;
        for (const depId of task.dependencies) {
            const depTask = State.tasks.find(t => t.id === depId);
            if (depTask) {
                total += this.calculateTotalHours(depTask, visited);
            }
        }

        return total;
    },

    // Auto-update task status when dependencies are resolved
    autoUpdateTaskStatus(taskId) {
        const task = State.tasks.find(t => t.id === taskId);
        if (!task || !task.dependencies || task.dependencies.length === 0) {
            return;
        }

        const allDependenciesResolved = task.dependencies.every(depId => {
            const depTask = State.tasks.find(t => t.id === depId);
            return depTask && depTask.status === 'DONE';
        });

        if (allDependenciesResolved && task.status === 'BLOCKED') {
            task.status = 'PENDING';
            State.addLog('TASK_UNBLOCKED', `Task "${task.title}" is now available for assignment`);
            if (typeof Renderer !== 'undefined') {
                Renderer.renderTasks();
            }
        } else if (!allDependenciesResolved && task.status === 'PENDING' && !task.assignedTo) {
            task.status = 'BLOCKED';
            State.addLog('TASK_BLOCKED', `Task "${task.title}" blocked by dependencies`);
            if (typeof Renderer !== 'undefined') {
                Renderer.renderTasks();
            }
        }
    },

    // Get all tasks that depend on a specific task
    getDependentTasks(taskId) {
        return State.tasks.filter(task => 
            task.dependencies && task.dependencies.includes(taskId)
        );
    },

    // Check if a task can be deleted (has no dependents)
    canDeleteTask(taskId) {
        const dependentTasks = this.getDependentTasks(taskId);
        if (dependentTasks.length > 0) {
            return {
                allowed: false,
                reason: `Cannot delete: ${dependentTasks.length} task(s) depend on this task`
            };
        }
        return { allowed: true };
    },

    // Validate if user can perform action on specific task
    canPerformTaskAction(task, action) {
        const actions = {
            'assign': () => Auth.can('canAssignTask'),
            'complete': () => {
                if (!Auth.can('canCompleteTask')) return false;
                if (Auth.isIntern()) {
                    return task.assignedTo === State.currentUser.internId;
                }
                return true;
            },
            'delete': () => Auth.isAdmin(),
            'edit': () => Auth.isAdmin()
        };

        return actions[action] ? actions[action]() : false;
    },

    // Validate if user can perform action on specific intern
    canPerformInternAction(intern, action) {
        const actions = {
            'activate': () => Auth.can('canActivateIntern') && intern.status === 'ONBOARDING',
            'exit': () => Auth.can('canExitIntern') && intern.status === 'ACTIVE',
            'edit': () => Auth.isAdmin(),
            'delete': () => Auth.isAdmin() && intern.assignedTasks.length === 0
        };

        return actions[action] ? actions[action]() : false;
    }
};