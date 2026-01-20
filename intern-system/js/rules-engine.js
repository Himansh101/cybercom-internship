const RulesEngine = {
    canAssignTask(task, intern) {
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

    canTransitionStatus(intern, newStatus) {
        return Validators.validateStatusTransition(intern.status, newStatus);
    },

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
            Renderer.renderTasks();
        } else if (!allDependenciesResolved && task.status === 'PENDING' && !task.assignedTo) {
            task.status = 'BLOCKED';
            State.addLog('TASK_BLOCKED', `Task "${task.title}" blocked by dependencies`);
            Renderer.renderTasks();
        }
    }
};