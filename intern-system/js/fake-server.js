const FakeServer = {
    async delay(ms = 500) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },

    async checkEmailUniqueness(email) {
        await this.delay(800);
        return !State.usedEmails.has(email.toLowerCase());
    },

    async validateInternData(data) {
        await this.delay(500);
        const errors = [];

        if (!data.name || data.name.trim().length < 2) {
            errors.push('Name must be at least 2 characters');
        }

        if (!data.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
            errors.push('Invalid email format');
        }

        if (!data.skills || data.skills.length === 0) {
            errors.push('At least one skill is required');
        }

        return errors;
    },

    async createInternRecord(data) {
        await this.delay(1000);

        const id = `${State.currentYear}_${String(State.nextInternId).padStart(3, '0')}`;
        State.nextInternId++;

        return {
            id,
            name: data.name,
            email: data.email.toLowerCase(),
            skills: data.skills,
            status: 'ONBOARDING',
            createdAt: new Date().toISOString(),
            assignedTasks: []
        };
    },

    async assignTaskToIntern(taskId, internId) {
        await this.delay(700);
        return { success: true };
    },

    async updateTaskStatus(taskId, status) {
        await this.delay(500);
        return { success: true };
    }
};