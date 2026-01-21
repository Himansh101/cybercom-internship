// app.js - Bootstrap & event wiring with permission checks

const App = {
    wireNavigation() {
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const view = e.target.dataset.view;
                
                // Check if user can access this section
                if (!Auth.canViewSection(view)) {
                    alert('You do not have permission to access this section');
                    return;
                }

                document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
                document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
                document.getElementById(view).classList.add('active');
                e.target.classList.add('active');
                State.currentView = view;
            });
        });
    },

    wireFilters() {
        const statusFilter = document.getElementById('status-filter');
        const skillFilter = document.getElementById('skill-filter');
        const internSearch = document.getElementById('intern-search');
        const taskSearch = document.getElementById('task-search');

        if (statusFilter) {
            statusFilter.addEventListener('change', () => Renderer.renderInterns());
        }

        if (skillFilter) {
            skillFilter.addEventListener('change', () => Renderer.renderInterns());
        }

        if (internSearch) {
            internSearch.addEventListener('input', () => Renderer.renderInterns());
        }

        if (taskSearch) {
            taskSearch.addEventListener('input', () => Renderer.renderTasks());
        }
    },

    wireForms() {
        // Intern form submission
        const internForm = document.getElementById('intern-form');
        if (internForm) {
            internForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Check permission before processing
                if (!RulesEngine.canCreateIntern()) {
                    alert('❌ You do not have permission to create interns');
                    return;
                }

                const errorDiv = document.getElementById('intern-error');
                errorDiv.innerHTML = '';

                try {
                    errorDiv.innerHTML = '<div class="loading">⏳ Creating intern...</div>';

                    const formData = {
                        name: e.target.name.value,
                        email: e.target.email.value,
                        skills: e.target.skills.value
                    };

                    const formErrors = Validators.validateInternForm(formData);
                    if (formErrors.length > 0) {
                        throw new Error(formErrors.join(', '));
                    }

                    const processedData = {
                        name: formData.name.trim(),
                        email: formData.email.trim(),
                        skills: formData.skills.split(',').map(s => s.trim()).filter(s => s)
                    };

                    const errors = await FakeServer.validateInternData(processedData);
                    if (errors.length > 0) {
                        throw new Error(errors.join(', '));
                    }

                    const isUnique = await FakeServer.checkEmailUniqueness(processedData.email);
                    if (!isUnique) {
                        throw new Error('Email already exists in the system');
                    }

                    const intern = await FakeServer.createInternRecord(processedData);

                    State.interns.push(intern);
                    State.usedEmails.add(processedData.email.toLowerCase());
                    State.addLog('INTERN_CREATED', `${intern.name} (${intern.id}) added to system`);

                    errorDiv.innerHTML = '<div class="success">✅ Intern created successfully!</div>';
                    e.target.reset();
                    Renderer.renderAll();

                    setTimeout(() => errorDiv.innerHTML = '', 3000);
                } catch (error) {
                    errorDiv.innerHTML = `<div class="error">❌ ${error.message}</div>`;
                }
            });
        }

        // Task form submission
        const taskForm = document.getElementById('task-form');
        if (taskForm) {
            taskForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Check permission before processing
                if (!RulesEngine.canCreateTask()) {
                    alert('❌ You do not have permission to create tasks');
                    return;
                }

                const errorDiv = document.getElementById('task-error');
                errorDiv.innerHTML = '';

                try {
                    errorDiv.innerHTML = '<div class="loading">⏳ Creating task...</div>';

                    const formData = {
                        title: e.target.title.value,
                        description: e.target.description.value,
                        skills: e.target.skills.value,
                        hours: parseInt(e.target.hours.value),
                        dependencies: e.target.dependencies.value
                    };

                    const formErrors = Validators.validateTaskForm(formData);
                    if (formErrors.length > 0) {
                        throw new Error(formErrors.join(', '));
                    }

                    const processedData = {
                        title: formData.title.trim(),
                        description: formData.description.trim(),
                        skills: formData.skills.split(',').map(s => s.trim()).filter(s => s),
                        hours: formData.hours,
                        dependencies: formData.dependencies
                            ? formData.dependencies.split(',').map(s => s.trim()).filter(s => s)
                            : []
                    };

                    if (processedData.dependencies.length > 0) {
                        const depValidation = Validators.validateTaskDependencies(null, processedData.dependencies);
                        if (!depValidation.valid) {
                            throw new Error(depValidation.error);
                        }
                    }

                    await FakeServer.delay(500);

                    const task = {
                        id: `TASK_${String(State.nextTaskId).padStart(3, '0')}`,
                        title: processedData.title,
                        description: processedData.description,
                        requiredSkills: processedData.skills,
                        estimatedHours: processedData.hours,
                        dependencies: processedData.dependencies,
                        assignedTo: null,
                        status: processedData.dependencies.length > 0 ? 'BLOCKED' : 'PENDING',
                        createdAt: new Date().toISOString()
                    };

                    State.nextTaskId++;
                    State.tasks.push(task);
                    State.addLog('TASK_CREATED', `Task "${task.title}" created (${task.estimatedHours}h estimated)`);

                    if (task.status === 'BLOCKED') {
                        State.addLog('TASK_BLOCKED', `Task "${task.title}" blocked by dependencies`);
                    }

                    errorDiv.innerHTML = '<div class="success">✅ Task created successfully!</div>';
                    e.target.reset();
                    Renderer.renderAll();

                    setTimeout(() => errorDiv.innerHTML = '', 3000);
                } catch (error) {
                    errorDiv.innerHTML = `<div class="error">❌ ${error.message}</div>`;
                }
            });
        }
    },

    async activateIntern(internId) {
        try {
            const intern = State.interns.find(i => i.id === internId);
            if (!intern) throw new Error('Intern not found');

            // Check if intern has skills
            if (!intern.skills || intern.skills.length === 0) {
                throw new Error('Please add skills before activating this intern');
            }

            // Check permission
            if (!RulesEngine.canPerformInternAction(intern, 'activate')) {
                throw new Error('You do not have permission to activate interns');
            }

            if (!RulesEngine.canTransitionStatus(intern, 'ACTIVE')) {
                throw new Error('Invalid status transition: Can only activate from ONBOARDING');
            }

            intern.status = 'ACTIVE';
            State.addLog('STATUS_CHANGED', `${intern.name} (${intern.id}) activated`);
            State.saveState(); // Save state immediately
            Renderer.renderAll();
            
            console.log(`✅ Intern ${intern.name} activated and saved to storage`);
        } catch (error) {
            alert(error.message);
        }
    },

    async addSkillsToIntern(internId) {
        try {
            const intern = State.interns.find(i => i.id === internId);
            if (!intern) throw new Error('Intern not found');

            // Check permission
            if (!Auth.isAdmin()) {
                throw new Error('Only admins can add skills to interns');
            }

            const skillsInput = document.getElementById(`skills-${internId}`);
            if (!skillsInput) throw new Error('Skills input not found');

            const skillsText = skillsInput.value.trim();
            if (!skillsText) {
                alert('Please enter at least one skill');
                return;
            }

            // Parse skills
            const skills = skillsText.split(',').map(s => s.trim()).filter(s => s);
            if (skills.length === 0) {
                alert('Please enter valid skills');
                return;
            }

            // Update intern skills
            intern.skills = skills;
            
            // Update email if it's auto-generated
            if (intern.email.endsWith('@intern.system') && !intern.email.includes('_')) {
                // Keep the auto-generated email
            }

            State.addLog('INTERN_UPDATED', `Skills added to ${intern.name} (${intern.id}): ${skills.join(', ')}`);
            State.saveState(); // Save to localStorage
            Renderer.renderAll();
        } catch (error) {
            alert(error.message);
        }
    },

    async exitIntern(internId) {
        if (!confirm('Are you sure you want to mark this intern as exited?')) return;

        try {
            const intern = State.interns.find(i => i.id === internId);
            if (!intern) throw new Error('Intern not found');

            // Check permission
            if (!RulesEngine.canPerformInternAction(intern, 'exit')) {
                throw new Error('You do not have permission to exit interns');
            }

            if (!RulesEngine.canTransitionStatus(intern, 'EXITED')) {
                throw new Error('Invalid status transition: Can only exit from ACTIVE');
            }

            intern.status = 'EXITED';
            State.addLog('STATUS_CHANGED', `${intern.name} (${intern.id}) exited`);
            State.saveState(); // Save to localStorage
            Renderer.renderAll();
        } catch (error) {
            alert(error.message);
        }
    },

    async assignTask(taskId) {
        const select = document.getElementById(`assign-${taskId}`);
        const internId = select?.value;

        if (!internId) {
            alert('Please select an intern');
            return;
        }

        try {
            const task = State.tasks.find(t => t.id === taskId);
            const intern = State.interns.find(i => i.id === internId);

            if (!task || !intern) {
                throw new Error('Task or Intern not found');
            }

            // Permission check is done inside canAssignTask
            const canAssign = RulesEngine.canAssignTask(task, intern);
            if (!canAssign.allowed) {
                throw new Error(canAssign.reason);
            }

            await FakeServer.assignTaskToIntern(taskId, internId);

            task.assignedTo = internId;
            task.status = 'IN_PROGRESS';
            intern.assignedTasks.push(taskId);

            State.addLog('TASK_ASSIGNED', `"${task.title}" assigned to ${intern.name} (${intern.id})`);
            State.saveState(); // Save to localStorage
            Renderer.renderAll();
        } catch (error) {
            alert(error.message);
        }
    },

    async completeTask(taskId) {
        try {
            const task = State.tasks.find(t => t.id === taskId);
            if (!task) throw new Error('Task not found');

            // Check permission before showing confirmation
            const canComplete = RulesEngine.canCompleteTask(task);
            if (!canComplete.allowed) {
                throw new Error(canComplete.reason);
            }

            if (!confirm('Mark this task as complete?')) return;

            await FakeServer.updateTaskStatus(taskId, 'DONE');

            task.status = 'DONE';
            State.addLog('TASK_COMPLETED', `Task "${task.title}" (${task.id}) marked as complete`);

            // Auto-update dependent tasks
            State.tasks.forEach(t => {
                if (t.dependencies && t.dependencies.includes(taskId)) {
                    RulesEngine.autoUpdateTaskStatus(t.id);
                }
            });

            State.saveState(); // Save to localStorage
            Renderer.renderAll();
        } catch (error) {
            alert(error.message);
        }
    },

    // Edit intern
    async editIntern(internId) {
        try {
            const intern = State.interns.find(i => i.id === internId);
            if (!intern) throw new Error('Intern not found');

            if (!Auth.isAdmin()) {
                throw new Error('Only admins can edit interns');
            }

            // Check if intern is exited
            if (intern.status === 'EXITED') {
                alert('❌ Cannot edit an exited intern. You can only delete exited interns.');
                return;
            }

            // Prompt for new details
            const newName = prompt('Edit Full Name:', intern.name);
            if (newName === null) return; // User cancelled

            const newEmail = prompt('Edit Email:', intern.email);
            if (newEmail === null) return;

            const newSkills = prompt('Edit Skills (comma-separated):', intern.skills.join(', '));
            if (newSkills === null) return;

            // Validate
            if (!newName.trim() || !newEmail.trim() || !newSkills.trim()) {
                alert('All fields are required');
                return;
            }

            // Check if email changed and if new email already exists
            const newEmailLower = newEmail.trim().toLowerCase();
            const oldEmailLower = intern.email.toLowerCase();
            
            if (newEmailLower !== oldEmailLower) {
                // Email changed - check uniqueness
                const emailExists = State.usedEmails.has(newEmailLower);
                if (emailExists) {
                    alert('This email is already in use. Please use a different email.');
                    return;
                }
                
                // Remove old email from set, add new one
                State.usedEmails.delete(oldEmailLower);
                State.usedEmails.add(newEmailLower);
            }

            // Update intern
            intern.name = newName.trim();
            intern.email = newEmail.trim();
            intern.skills = newSkills.split(',').map(s => s.trim()).filter(s => s);

            State.addLog('INTERN_UPDATED', `${intern.name} (${intern.id}) details updated`);
            State.saveState(); // Explicitly save to localStorage
            Renderer.renderAll();

            alert('✅ Intern updated successfully!');
        } catch (error) {
            alert(error.message);
        }
    },

    // Delete intern
    async deleteIntern(internId) {
        try {
            const intern = State.interns.find(i => i.id === internId);
            if (!intern) throw new Error('Intern not found');

            if (!Auth.isAdmin()) {
                throw new Error('Only admins can delete interns');
            }

            // Check if intern has assigned tasks
            if (intern.assignedTasks && intern.assignedTasks.length > 0) {
                alert(`Cannot delete ${intern.name}. They have ${intern.assignedTasks.length} assigned task(s). Please reassign or complete their tasks first.`);
                return;
            }

            // Confirm deletion
            if (!confirm(`Are you sure you want to delete ${intern.name}? This action cannot be undone.`)) {
                return;
            }

            // Remove intern from array
            const index = State.interns.indexOf(intern);
            State.interns.splice(index, 1);

            // Remove email from used emails
            State.usedEmails.delete(intern.email.toLowerCase());

            State.addLog('INTERN_DELETED', `${intern.name} (${intern.id}) deleted from system`);
            State.saveState(); // Explicitly save to localStorage
            Renderer.renderAll();

            alert('✅ Intern deleted successfully!');
        } catch (error) {
            alert(error.message);
        }
    },

    // Edit task
    async editTask(taskId) {
        try {
            const task = State.tasks.find(t => t.id === taskId);
            if (!task) throw new Error('Task not found');

            if (!Auth.isAdmin()) {
                throw new Error('Only admins can edit tasks');
            }

            // Check if task is completed
            if (task.status === 'DONE') {
                alert('❌ Cannot edit a completed task. You can only delete completed tasks.');
                return;
            }

            // Prompt for new details
            const newTitle = prompt('Edit Task Title:', task.title);
            if (newTitle === null) return; // User cancelled

            const newDescription = prompt('Edit Description:', task.description);
            if (newDescription === null) return;

            const newSkills = prompt('Edit Required Skills (comma-separated):', task.requiredSkills.join(', '));
            if (newSkills === null) return;

            const newHours = prompt('Edit Estimated Hours:', task.estimatedHours);
            if (newHours === null) return;

            // Validate
            if (!newTitle.trim() || !newDescription.trim() || !newSkills.trim()) {
                alert('Title, description, and skills are required');
                return;
            }

            const hours = parseInt(newHours);
            if (isNaN(hours) || hours < 1) {
                alert('Hours must be a positive number');
                return;
            }

            // Update task
            task.title = newTitle.trim();
            task.description = newDescription.trim();
            task.requiredSkills = newSkills.split(',').map(s => s.trim()).filter(s => s);
            task.estimatedHours = hours;

            State.addLog('TASK_UPDATED', `Task "${task.title}" (${task.id}) updated`);
            State.saveState(); // Explicitly save to localStorage
            Renderer.renderAll();

            alert('✅ Task updated successfully!');
        } catch (error) {
            alert(error.message);
        }
    },

    // Delete task
    async deleteTask(taskId) {
        try {
            const task = State.tasks.find(t => t.id === taskId);
            if (!task) throw new Error('Task not found');

            if (!Auth.isAdmin()) {
                throw new Error('Only admins can delete tasks');
            }

            // Allow deletion if task is DONE or if it's unassigned PENDING
            const canDelete = task.status === 'DONE' || (!task.assignedTo && task.status === 'PENDING');
            
            if (!canDelete) {
                alert('Can only delete unassigned PENDING tasks or DONE tasks.');
                return;
            }

            // Check if other tasks depend on this task (only for non-DONE tasks)
            if (task.status !== 'DONE') {
                const dependentTasks = State.tasks.filter(t =>
                    t.dependencies && t.dependencies.includes(taskId)
                );

                if (dependentTasks.length > 0) {
                    alert(`Cannot delete this task. ${dependentTasks.length} other task(s) depend on it: ${dependentTasks.map(t => t.title).join(', ')}`);
                    return;
                }
            }

            // Confirm deletion
            if (!confirm(`Are you sure you want to delete "${task.title}"? This action cannot be undone.`)) {
                return;
            }

            // Remove task from array
            const index = State.tasks.indexOf(task);
            State.tasks.splice(index, 1);

            // If task was assigned, remove from intern's assignedTasks
            if (task.assignedTo) {
                const intern = State.interns.find(i => i.id === task.assignedTo);
                if (intern) {
                    const taskIndex = intern.assignedTasks.indexOf(taskId);
                    if (taskIndex > -1) {
                        intern.assignedTasks.splice(taskIndex, 1);
                    }
                }
            }

            State.addLog('TASK_DELETED', `Task "${task.title}" (${task.id}) deleted from system`);
            State.saveState(); // Explicitly save to localStorage
            Renderer.renderAll();

            alert('✅ Task deleted successfully!');
        } catch (error) {
            alert(error.message);
        }
    }
};

// Don't auto-initialize on page load
// App methods will be called by Auth.login() after successful authentication
console.log('✅ App ready - waiting for login...');