// const App = {
//     wireNavigation() {
//         document.querySelectorAll('.nav-btn').forEach(btn => {
//             btn.addEventListener('click', (e) => {
//                 const view = e.target.dataset.view;

//                 // Check if user can access this section
//                 if (!Auth.canViewSection(view)) {
//                     alert('You do not have permission to access this section');
//                     return;
//                 }

//                 document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
//                 document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
//                 document.getElementById(view).classList.add('active');
//                 e.target.classList.add('active');
//                 State.currentView = view;
//             });
//         });
//     },

//     wireFilters() {
//         const statusFilter = document.getElementById('status-filter');
//         const skillFilter = document.getElementById('skill-filter');

//         if (statusFilter) {
//             statusFilter.addEventListener('change', () => Renderer.renderInterns());
//         }

//         if (skillFilter) {
//             skillFilter.addEventListener('change', () => Renderer.renderInterns());
//         }
//     },

//     wireForms() {
//         // Intern form submission
//         const internForm = document.getElementById('intern-form');
//         if (internForm) {
//             internForm.addEventListener('submit', async (e) => {
//                 e.preventDefault();

//                 // Check permission before processing
//                 if (!RulesEngine.canCreateIntern()) {
//                     alert('❌ You do not have permission to create interns');
//                     return;
//                 }

//                 const errorDiv = document.getElementById('intern-error');
//                 errorDiv.innerHTML = '';

//                 try {
//                     errorDiv.innerHTML = '<div class="loading">⏳ Creating intern...</div>';

//                     const formData = {
//                         name: e.target.name.value,
//                         email: e.target.email.value,
//                         skills: e.target.skills.value
//                     };

//                     const formErrors = Validators.validateInternForm(formData);
//                     if (formErrors.length > 0) {
//                         throw new Error(formErrors.join(', '));
//                     }

//                     const processedData = {
//                         name: formData.name.trim(),
//                         email: formData.email.trim(),
//                         skills: formData.skills.split(',').map(s => s.trim()).filter(s => s)
//                     };

//                     const errors = await FakeServer.validateInternData(processedData);
//                     if (errors.length > 0) {
//                         throw new Error(errors.join(', '));
//                     }

//                     const isUnique = await FakeServer.checkEmailUniqueness(processedData.email);
//                     if (!isUnique) {
//                         throw new Error('Email already exists in the system');
//                     }

//                     const intern = await FakeServer.createInternRecord(processedData);

//                     State.interns.push(intern);
//                     State.usedEmails.add(processedData.email.toLowerCase());
//                     State.addLog('INTERN_CREATED', `${intern.name} (${intern.id}) added to system`);

//                     errorDiv.innerHTML = '<div class="success">✅ Intern created successfully!</div>';
//                     e.target.reset();
//                     Renderer.renderAll();

//                     setTimeout(() => errorDiv.innerHTML = '', 3000);
//                 } catch (error) {
//                     errorDiv.innerHTML = `<div class="error">❌ ${error.message}</div>`;
//                 }
//             });
//         }

//         // Task form submission
//         const taskForm = document.getElementById('task-form');
//         if (taskForm) {
//             taskForm.addEventListener('submit', async (e) => {
//                 e.preventDefault();

//                 // Check permission before processing
//                 if (!RulesEngine.canCreateTask()) {
//                     alert('❌ You do not have permission to create tasks');
//                     return;
//                 }

//                 const errorDiv = document.getElementById('task-error');
//                 errorDiv.innerHTML = '';

//                 try {
//                     errorDiv.innerHTML = '<div class="loading">⏳ Creating task...</div>';

//                     const formData = {
//                         title: e.target.title.value,
//                         description: e.target.description.value,
//                         skills: e.target.skills.value,
//                         hours: parseInt(e.target.hours.value),
//                         dependencies: e.target.dependencies.value
//                     };

//                     const formErrors = Validators.validateTaskForm(formData);
//                     if (formErrors.length > 0) {
//                         throw new Error(formErrors.join(', '));
//                     }

//                     const processedData = {
//                         title: formData.title.trim(),
//                         description: formData.description.trim(),
//                         skills: formData.skills.split(',').map(s => s.trim()).filter(s => s),
//                         hours: formData.hours,
//                         dependencies: formData.dependencies
//                             ? formData.dependencies.split(',').map(s => s.trim()).filter(s => s)
//                             : []
//                     };

//                     if (processedData.dependencies.length > 0) {
//                         const depValidation = Validators.validateTaskDependencies(null, processedData.dependencies);
//                         if (!depValidation.valid) {
//                             throw new Error(depValidation.error);
//                         }
//                     }

//                     await FakeServer.delay(500);

//                     const task = {
//                         id: `TASK_${String(State.nextTaskId).padStart(3, '0')}`,
//                         title: processedData.title,
//                         description: processedData.description,
//                         requiredSkills: processedData.skills,
//                         estimatedHours: processedData.hours,
//                         dependencies: processedData.dependencies,
//                         assignedTo: null,
//                         status: processedData.dependencies.length > 0 ? 'BLOCKED' : 'PENDING',
//                         createdAt: new Date().toISOString()
//                     };

//                     State.nextTaskId++;
//                     State.tasks.push(task);
//                     State.addLog('TASK_CREATED', `Task "${task.title}" created (${task.estimatedHours}h estimated)`);

//                     if (task.status === 'BLOCKED') {
//                         State.addLog('TASK_BLOCKED', `Task "${task.title}" blocked by dependencies`);
//                     }

//                     errorDiv.innerHTML = '<div class="success">✅ Task created successfully!</div>';
//                     e.target.reset();
//                     Renderer.renderAll();

//                     setTimeout(() => errorDiv.innerHTML = '', 3000);
//                 } catch (error) {
//                     errorDiv.innerHTML = `<div class="error">❌ ${error.message}</div>`;
//                 }
//             });
//         }
//     },

//     async activateIntern(internId) {
//         try {
//             const intern = State.interns.find(i => i.id === internId);
//             if (!intern) throw new Error('Intern not found');

//             // Check permission
//             if (!RulesEngine.canPerformInternAction(intern, 'activate')) {
//                 throw new Error('You do not have permission to activate interns');
//             }

//             if (!RulesEngine.canTransitionStatus(intern, 'ACTIVE')) {
//                 throw new Error('Invalid status transition: Can only activate from ONBOARDING');
//             }

//             intern.status = 'ACTIVE';
//             State.addLog('STATUS_CHANGED', `${intern.name} (${intern.id}) activated`);
//             Renderer.renderAll();
//         } catch (error) {
//             alert(error.message);
//         }
//     },

//     async exitIntern(internId) {
//         if (!confirm('Are you sure you want to mark this intern as exited?')) return;

//         try {
//             const intern = State.interns.find(i => i.id === internId);
//             if (!intern) throw new Error('Intern not found');

//             // Check permission
//             if (!RulesEngine.canPerformInternAction(intern, 'exit')) {
//                 throw new Error('You do not have permission to exit interns');
//             }

//             if (!RulesEngine.canTransitionStatus(intern, 'EXITED')) {
//                 throw new Error('Invalid status transition: Can only exit from ACTIVE');
//             }

//             intern.status = 'EXITED';
//             State.addLog('STATUS_CHANGED', `${intern.name} (${intern.id}) exited`);
//             Renderer.renderAll();
//         } catch (error) {
//             alert(error.message);
//         }
//     },

//     async assignTask(taskId) {
//         const select = document.getElementById(`assign-${taskId}`);
//         const internId = select?.value;

//         if (!internId) {
//             alert('Please select an intern');
//             return;
//         }

//         try {
//             const task = State.tasks.find(t => t.id === taskId);
//             const intern = State.interns.find(i => i.id === internId);

//             if (!task || !intern) {
//                 throw new Error('Task or Intern not found');
//             }

//             // Permission check is done inside canAssignTask
//             const canAssign = RulesEngine.canAssignTask(task, intern);
//             if (!canAssign.allowed) {
//                 throw new Error(canAssign.reason);
//             }

//             await FakeServer.assignTaskToIntern(taskId, internId);

//             task.assignedTo = internId;
//             task.status = 'IN_PROGRESS';
//             intern.assignedTasks.push(taskId);

//             State.addLog('TASK_ASSIGNED', `"${task.title}" assigned to ${intern.name} (${intern.id})`);
//             Renderer.renderAll();
//         } catch (error) {
//             alert(error.message);
//         }
//     },

//     async completeTask(taskId) {
//         try {
//             const task = State.tasks.find(t => t.id === taskId);
//             if (!task) throw new Error('Task not found');

//             // Check permission before showing confirmation
//             const canComplete = RulesEngine.canCompleteTask(task);
//             if (!canComplete.allowed) {
//                 throw new Error(canComplete.reason);
//             }

//             if (!confirm('Mark this task as complete?')) return;

//             await FakeServer.updateTaskStatus(taskId, 'DONE');

//             task.status = 'DONE';
//             State.addLog('TASK_COMPLETED', `Task "${task.title}" (${task.id}) marked as complete`);

//             // Auto-update dependent tasks
//             State.tasks.forEach(t => {
//                 if (t.dependencies && t.dependencies.includes(taskId)) {
//                     RulesEngine.autoUpdateTaskStatus(t.id);
//                 }
//             });

//             Renderer.renderAll();
//         } catch (error) {
//             alert(error.message);
//         }
//     }
// };

// // Don't auto-initialize on page load
// // App methods will be called by Auth.login() after successful authentication
// console.log('✅ App ready - waiting for login...');

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

        if (statusFilter) {
            statusFilter.addEventListener('change', () => Renderer.renderInterns());
        }

        if (skillFilter) {
            skillFilter.addEventListener('change', () => Renderer.renderInterns());
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
    }
};

// Don't auto-initialize on page load
// App methods will be called by Auth.login() after successful authentication
console.log('✅ App ready - waiting for login...');