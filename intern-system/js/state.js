const State = {
    interns: [],
    tasks: [],
    logs: [],
    usedEmails: new Set(),
    currentView: 'dashboard',
    nextInternId: 1,
    nextTaskId: 1,
    currentYear: new Date().getFullYear(),

    update(updates) {
        Object.assign(this, updates);
        Renderer.renderAll();
    },

    addLog(action, details) {
        this.logs.unshift({
            timestamp: new Date().toISOString(),
            action,
            details
        });
        if (this.logs.length > 100) {
            this.logs = this.logs.slice(0, 100);
        }
        Renderer.renderLogs();
        Renderer.renderStats();
    }
};