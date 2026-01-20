const App = {
    init() {
        this.wireNavigation();
        this.wireFilters();
        this.wireForms();
        Renderer.renderAll();
        console.log('✅ Intern Operations System Initialized');
    },

    wireNavigation() {
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const view = e.target.dataset.view;
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

        if (statusFilter) {
            statusFilter.addEventListener('change', () => Renderer.renderInterns());
        }

        if (skillFilter) {
            skillFilter.addEventListener('change', () => Renderer.renderInterns());
        }
    },

    wireForms() {
        document.getElementById('intern-form').addEventListener('submit', async (e) => {
            e.preventDefault();
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

        document.getElementById('task-form').addEventListener('submit', async (e) => {
            e.preventDefault();
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
    },

    async activateIntern(internId) {
        try {
            const intern = State.interns.find(i => i.id === internId);
            if (!intern) throw new Error('Intern not found');

            if (!RulesEngine.canTransitionStatus(intern, 'ACTIVE')) {
                throw new Error('Invalid status transition: Can only activate from ONBOARDING');
            }

            intern.status = 'ACTIVE';
            State.addLog('STATUS_CHANGED', `${intern.name} (${intern.id}) activated`);
            Renderer.renderAll();
        } catch (error) {
            alert(error.message);
        }
    },

    async exitIntern(internId) {
        if (confirm('Are you sure you want to mark this intern as exited?')) {
            try {
                const intern = State.interns.find(i => i.id === internId);
                if (!intern) throw new Error('Intern not found');

                if (!RulesEngine.canTransitionStatus(intern, 'EXITED')) {
                    throw new Error('Invalid status transition: Can only exit from ACTIVE');
                }

                intern.status = 'EXITED';
                State.addLog('STATUS_CHANGED', `${intern.name} (${intern.id}) exited`);
                Renderer.renderAll();
            } catch (error) {
                alert(error.message);
            }
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

            const canAssign = RulesEngine.canAssignTask(task, intern);
            if (!canAssign.allowed) {
                throw new Error(canAssign.reason);
            }

            await FakeServer.assignTaskToIntern(taskId, internId);

            task.assignedTo = internId;
            task.status = 'IN_PROGRESS';
            intern.assignedTasks.push(taskId);

            State.addLog('TASK_ASSIGNED', `"${task.title}" assigned to ${intern.name} (${intern.id})`);
            Renderer.renderAll();
        } catch (error) {
            alert(error.message);
        }
    },

    async completeTask(taskId) {
        if (!confirm('Mark this task as complete?')) return;

        try {
            const task = State.tasks.find(t => t.id === taskId);
            if (!task) throw new Error('Task not found');

            await FakeServer.updateTaskStatus(taskId, 'DONE');

            task.status = 'DONE';
            State.addLog('TASK_COMPLETED', `Task "${task.title}" (${task.id}) marked as complete`);

            State.tasks.forEach(t => {
                if (t.dependencies && t.dependencies.includes(taskId)) {
                    RulesEngine.autoUpdateTaskStatus(t.id);
                }
            });

            Renderer.renderAll();
        } catch (error) {
            alert(error.message);
        }
    }
};

App.init();