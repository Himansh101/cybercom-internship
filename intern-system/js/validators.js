const Validators = {
    validateInternForm(formData) {
        const errors = [];

        if (!formData.name || !formData.name.trim()) {
            errors.push('Name is required');
        }

        if (!formData.email || !formData.email.trim()) {
            errors.push('Email is required');
        }

        if (!formData.skills || !formData.skills.trim()) {
            errors.push('At least one skill is required');
        }

        return errors;
    },

    validateTaskForm(formData) {
        const errors = [];

        if (!formData.title || !formData.title.trim()) {
            errors.push('Title is required');
        }

        if (!formData.description || !formData.description.trim()) {
            errors.push('Description is required');
        }

        if (!formData.skills || !formData.skills.trim()) {
            errors.push('At least one skill is required');
        }

        if (!formData.hours || formData.hours < 1) {
            errors.push('Hours must be at least 1');
        }

        return errors;
    },

    validateStatusTransition(currentStatus, newStatus) {
        const validTransitions = {
            'ONBOARDING': ['ACTIVE'],
            'ACTIVE': ['EXITED'],
            'EXITED': []
        };

        return validTransitions[currentStatus]?.includes(newStatus) || false;
    },

    validateTaskDependencies(taskId, dependencyIds) {
        for (const depId of dependencyIds) {
            const depTask = State.tasks.find(t => t.id === depId);
            if (!depTask) {
                return { valid: false, error: `Task ${depId} not found` };
            }
        }

        const circular = this.detectCircularDependency(taskId, dependencyIds);
        if (circular) {
            return {
                valid: false,
                error: `Circular dependency detected: ${circular.join(' → ')}`
            };
        }

        return { valid: true };
    },

    detectCircularDependency(taskId, newDependencies, visited = new Set(), path = []) {
        if (visited.has(taskId)) {
            return [...path, taskId];
        }

        visited.add(taskId);
        path.push(taskId);

        for (const depId of newDependencies) {
            const depTask = State.tasks.find(t => t.id === depId);
            if (depTask && depTask.dependencies && depTask.dependencies.length > 0) {
                const circular = this.detectCircularDependency(
                    depId,
                    depTask.dependencies,
                    new Set(visited),
                    [...path]
                );
                if (circular) return circular;
            }
        }

        return null;
    }
};