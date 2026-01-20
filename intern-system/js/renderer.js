// renderer.js - DOM updates with role-based visibility

const Renderer = {
    renderAll() {
        this.renderStats();
        this.renderInterns();
        this.renderTasks();
        this.renderLogs();
        this.updateSkillFilter();
        this.applyRoleBasedVisibility();
    },

    renderStats() {
        const stats = document.getElementById('stats');

        // Use filtered data based on role
        const visibleInterns = State.getVisibleInterns();
        const visibleTasks = State.getVisibleTasks();

        const total = visibleInterns.length;
        const active = visibleInterns.filter(i => i.status === 'ACTIVE').length;
        const onboarding = visibleInterns.filter(i => i.status === 'ONBOARDING').length;
        const tasks = visibleTasks.length;
        const unassigned = visibleTasks.filter(t => !t.assignedTo && t.status !== 'BLOCKED').length;
        const blocked = visibleTasks.filter(t => t.status === 'BLOCKED').length;
        const completed = visibleTasks.filter(t => t.status === 'DONE').length;

        stats.innerHTML = `
            <div class="stat-card">
                <h3>${total}</h3>
                <p>Total Interns</p>
            </div>
            <div class="stat-card">
                <h3>${active}</h3>
                <p>Active Interns</p>
            </div>
            <div class="stat-card">
                <h3>${onboarding}</h3>
                <p>Onboarding</p>
            </div>
            <div class="stat-card">
                <h3>${tasks}</h3>
                <p>Total Tasks</p>
            </div>
            <div class="stat-card">
                <h3>${unassigned}</h3>
                <p>Available Tasks</p>
            </div>
            <div class="stat-card">
                <h3>${blocked}</h3>
                <p>Blocked Tasks</p>
            </div>
            <div class="stat-card">
                <h3>${completed}</h3>
                <p>Completed</p>
            </div>
        `;
    },

    renderInterns() {
        const list = document.getElementById('intern-list');
        const statusFilter = document.getElementById('status-filter')?.value || 'all';
        const skillFilter = document.getElementById('skill-filter')?.value || 'all';

        // Use filtered data based on role
        let filtered = State.getVisibleInterns();

        if (statusFilter !== 'all') {
            filtered = filtered.filter(i => i.status === statusFilter);
        }

        if (skillFilter !== 'all') {
            filtered = filtered.filter(i =>
                i.skills.some(s => s.toLowerCase() === skillFilter.toLowerCase())
            );
        }

        if (filtered.length === 0) {
            list.innerHTML = '<div class="empty-state"><p>No interns found</p></div>';
            return;
        }

        list.innerHTML = filtered.map(intern => `
            <div class="intern-card">
                <h3>${intern.name}</h3>
                <p><strong>ID:</strong> ${intern.id}</p>
                <p><strong>Email:</strong> ${intern.email}</p>
                <p><strong>Status:</strong> <span class="status-badge status-${intern.status.toLowerCase()}">${intern.status}</span></p>
                <div class="skill-tags">
                    ${intern.skills.map(s => `<span class="skill-tag">${s}</span>`).join('')}
                </div>
                <p style="margin-top: 10px;"><strong>Assigned Tasks:</strong> ${intern.assignedTasks.length}</p>
                ${this.renderInternActions(intern)}
            </div>
        `).join('');
    },

    renderInternActions(intern) {
        let actions = '';

        // Show activate button only if user has permission
        if (intern.status === 'ONBOARDING' && RulesEngine.canPerformInternAction(intern, 'activate')) {
            actions += `<button class="btn btn-success" onclick="App.activateIntern('${intern.id}')">Activate</button>`;
        }

        // Show exit button only if user has permission
        if (intern.status === 'ACTIVE' && RulesEngine.canPerformInternAction(intern, 'exit')) {
            actions += `<button class="btn btn-danger" onclick="App.exitIntern('${intern.id}')">Mark as Exited</button>`;
        }

        return actions;
    },

    renderTasks() {
        const list = document.getElementById('task-list');
        const taskListHeader = document.getElementById('task-list-header');

        // Update header based on role
        if (taskListHeader) {
            taskListHeader.textContent = Auth.isIntern() ? 'My Tasks' : 'Task List';
        }

        // Use filtered data based on role
        const visibleTasks = State.getVisibleTasks();

        if (visibleTasks.length === 0) {
            const emptyMessage = Auth.isIntern()
                ? 'No tasks assigned to you yet'
                : 'No tasks created yet';
            list.innerHTML = `<div class="empty-state"><p>${emptyMessage}</p></div>`;
            return;
        }

        list.innerHTML = visibleTasks.map(task => {
            const assignedIntern = task.assignedTo ?
                State.interns.find(i => i.id === task.assignedTo) : null;

            const totalHours = RulesEngine.calculateTotalHours(task);

            return `
                <li class="task-item">
                    <div class="task-header">
                        <div>
                            <strong>${task.title}</strong>
                            <span class="task-status task-${task.status.toLowerCase()}" style="margin-left: 10px;">${task.status}</span>
                        </div>
                        <span style="color: #6c757d; font-size: 13px;">${task.id}</span>
                    </div>
                    <p style="color: #495057; margin-bottom: 10px;">${task.description}</p>
                    <div class="skill-tags">
                        ${task.requiredSkills.map(s => `<span class="skill-tag">${s}</span>`).join('')}
                    </div>
                    ${task.dependencies && task.dependencies.length > 0 ? `
                        <div class="dependency-list">
                            <strong>⚠️ Dependencies:</strong>
                            ${task.dependencies.map(depId => {
                const depTask = State.tasks.find(t => t.id === depId);
                return `<span class="dependency-tag">${depId} ${depTask ? `(${depTask.status})` : '(NOT FOUND)'}</span>`;
            }).join('')}
                        </div>
                    ` : ''}
                    <div class="hours-display">
                        <strong>⏱️ Estimated Hours:</strong> ${task.estimatedHours}h
                        ${totalHours > task.estimatedHours ? ` | <strong>Total (with deps):</strong> ${totalHours}h` : ''}
                    </div>
                    <p style="margin-top: 10px; color: #495057;">
                        <strong>Assigned to:</strong> 
                        ${assignedIntern ? `${assignedIntern.name} (${assignedIntern.id})` : '<em>Unassigned</em>'}
                    </p>
                    ${this.renderTaskActions(task)}
                </li>
            `;
        }).join('');
    },

    renderTaskActions(task) {
        let actions = '';

        // Show assignment dropdown only if user can assign tasks
        if (!task.assignedTo && task.status !== 'BLOCKED' && Auth.can('canAssignTask')) {
            const activeInterns = State.interns.filter(i => i.status === 'ACTIVE');

            if (activeInterns.length > 0) {
                actions += `
                    <div style="margin-top: 12px;">
                        <select id="assign-${task.id}" style="padding: 8px 12px; margin-right: 10px; border: 1px solid #ced4da; border-radius: 4px;">
                            <option value="">Select intern...</option>
                            ${activeInterns.map(i =>
                    `<option value="${i.id}">${i.name} - ${i.skills.join(', ')}</option>`
                ).join('')}
                        </select>
                        <button class="btn btn-primary" onclick="App.assignTask('${task.id}')">Assign Task</button>
                    </div>
                `;
            } else {
                actions += `
                    <p style="margin-top: 10px; color: #856404; background: #fff3cd; padding: 10px; border-radius: 4px; font-size: 14px;">
                        ⚠️ No active interns available. Please activate an intern first.
                    </p>
                `;
            }
        }

        // Show blocked message
        if (task.status === 'BLOCKED') {
            actions += `
                <p style="margin-top: 10px; color: #dc3545; font-weight: 600;">
                    🚫 This task is blocked. Complete dependencies first.
                </p>
            `;
        }

        // Show complete button only if user can complete this specific task
        if (task.assignedTo && task.status === 'IN_PROGRESS') {
            const canComplete = RulesEngine.canCompleteTask(task);
            if (canComplete.allowed) {
                actions += `
                    <button class="btn btn-success" onclick="App.completeTask('${task.id}')">✓ Mark as Complete</button>
                `;
            }
        }

        return actions;
    },

    renderLogs() {
        const list = document.getElementById('log-list');

        // Use filtered logs based on role
        const visibleLogs = State.getVisibleLogs();

        if (visibleLogs.length === 0) {
            list.innerHTML = '<div class="empty-state"><p>No activity logs yet</p></div>';
            return;
        }

        list.innerHTML = visibleLogs.slice(0, 50).map(log => `
            <div class="log-entry">
                <div class="log-time">${new Date(log.timestamp).toLocaleString()}</div>
                <strong>${log.action}:</strong> ${log.details}
                ${log.userRole ? `<span style="margin-left: 10px; color: #6c757d; font-size: 12px;">[${log.userRole}]</span>` : ''}
            </div>
        `).join('');
    },

    updateSkillFilter() {
        const skillFilter = document.getElementById('skill-filter');
        if (!skillFilter) return;

        const allSkills = new Set();
        State.getVisibleInterns().forEach(i => i.skills.forEach(s => allSkills.add(s)));

        const currentValue = skillFilter.value;
        skillFilter.innerHTML = '<option value="all">All Skills</option>' +
            Array.from(allSkills).sort().map(s =>
                `<option value="${s}" ${s === currentValue ? 'selected' : ''}>${s}</option>`
            ).join('');
    },

    // Apply role-based visibility to form sections
    applyRoleBasedVisibility() {
        // Hide/show create intern form based on permission
        const createInternCard = document.getElementById('create-intern-card');
        if (createInternCard) {
            if (Auth.can('canCreateIntern')) {
                createInternCard.classList.remove('hidden');
            } else {
                createInternCard.classList.add('hidden');
            }
        }

        // Hide/show create task form based on permission
        const createTaskCard = document.getElementById('create-task-card');
        if (createTaskCard) {
            if (Auth.can('canCreateTask')) {
                createTaskCard.classList.remove('hidden');
            } else {
                createTaskCard.classList.add('hidden');
            }
        }
    }
};