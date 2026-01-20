const Renderer = {
    renderAll() {
        this.renderStats();
        this.renderInterns();
        this.renderTasks();
        this.renderLogs();
        this.updateSkillFilter();
    },

    renderStats() {
        const stats = document.getElementById('stats');
        const total = State.interns.length;
        const active = State.interns.filter(i => i.status === 'ACTIVE').length;
        const onboarding = State.interns.filter(i => i.status === 'ONBOARDING').length;
        const tasks = State.tasks.length;
        const unassigned = State.tasks.filter(t => !t.assignedTo && t.status !== 'BLOCKED').length;
        const blocked = State.tasks.filter(t => t.status === 'BLOCKED').length;
        const completed = State.tasks.filter(t => t.status === 'DONE').length;

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

        let filtered = State.interns;

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
                        ${intern.status === 'ONBOARDING' ?
                `<button class="btn btn-success" onclick="App.activateIntern('${intern.id}')">Activate</button>` : ''}
                        ${intern.status === 'ACTIVE' ?
                `<button class="btn btn-danger" onclick="App.exitIntern('${intern.id}')">Mark as Exited</button>` : ''}
                    </div>
                `).join('');
    },

    renderTasks() {
        const list = document.getElementById('task-list');

        if (State.tasks.length === 0) {
            list.innerHTML = '<div class="empty-state"><p>No tasks created yet</p></div>';
            return;
        }

        list.innerHTML = State.tasks.map(task => {
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
                            ${!task.assignedTo && task.status !== 'BLOCKED' ? `
                                <div style="margin-top: 12px;">
                                    <select id="assign-${task.id}" style="padding: 8px 12px; margin-right: 10px; border: 1px solid #ced4da; border-radius: 4px;">
                                        <option value="">Select intern...</option>
                                        ${State.interns.filter(i => i.status === 'ACTIVE').map(i =>
                `<option value="${i.id}">${i.name} - ${i.skills.join(', ')}</option>`
            ).join('')}
                                    </select>
                                    <button class="btn btn-primary" onclick="App.assignTask('${task.id}')">Assign Task</button>
                                </div>
                            ` : ''}
                            ${task.status === 'BLOCKED' ? `
                                <p style="margin-top: 10px; color: #dc3545; font-weight: 600;">
                                    🚫 This task is blocked. Complete dependencies first.
                                </p>
                            ` : ''}
                            ${task.assignedTo && task.status === 'IN_PROGRESS' ? `
                                <button class="btn btn-success" onclick="App.completeTask('${task.id}')">✓ Mark as Complete</button>
                            ` : ''}
                        </li>
                    `;
        }).join('');
    },

    renderLogs() {
        const list = document.getElementById('log-list');

        if (State.logs.length === 0) {
            list.innerHTML = '<div class="empty-state"><p>No activity logs yet</p></div>';
            return;
        }

        list.innerHTML = State.logs.slice(0, 50).map(log => `
                    <div class="log-entry">
                        <div class="log-time">${new Date(log.timestamp).toLocaleString()}</div>
                        <strong>${log.action}:</strong> ${log.details}
                    </div>
                `).join('');
    },

    updateSkillFilter() {
        const skillFilter = document.getElementById('skill-filter');
        if (!skillFilter) return;

        const allSkills = new Set();
        State.interns.forEach(i => i.skills.forEach(s => allSkills.add(s)));

        const currentValue = skillFilter.value;
        skillFilter.innerHTML = '<option value="all">All Skills</option>' +
            Array.from(allSkills).sort().map(s =>
                `<option value="${s}" ${s === currentValue ? 'selected' : ''}>${s}</option>`
            ).join('');
    }
};