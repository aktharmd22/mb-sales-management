import './bootstrap';
import ApexCharts from 'apexcharts';
import Sortable from 'sortablejs';

window.ApexCharts = ApexCharts;
window.Sortable = Sortable;

// Reusable Alpine wrapper for ApexCharts. Paired with a changing wire:key so
// Livewire replaces the element (and Alpine re-inits) whenever data changes.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('apexChart', (options) => ({
        chart: null,
        init() {
            this.chart = new ApexCharts(this.$el, options);
            this.chart.render();
            this.$cleanup(() => this.chart && this.chart.destroy());
        },
    }));

    // Portfolio add/edit modal — drives a plain POST form (reliable uploads).
    window.Alpine.data('portfolioForm', (config) => ({
        open: false,
        editId: null,
        formType: config.tab,
        fields: { title: '', description: '', url: '', credentials: [] },
        init() {
            // Re-open with old input if the server bounced back validation errors.
            if (config.hasErrors) {
                this.formType = config.old.type || config.tab;
                this.fields.title = config.old.title || '';
                this.fields.description = config.old.description || '';
                this.fields.url = config.old.url || '';
                this.fields.credentials = Array.isArray(config.old.credentials) ? config.old.credentials : [];
                this.open = true;
            }
        },
        create(type) {
            this.editId = null;
            this.formType = type;
            this.fields = { title: '', description: '', url: '', credentials: [] };
            if (type === 'website') this.addCred();
            this.open = true;
        },
        edit(item) {
            this.editId = item.id;
            this.formType = item.type;
            this.fields = {
                title: item.title || '',
                description: item.description || '',
                url: item.url || '',
                credentials: (item.credentials || []).map((c) => ({ ...c })),
            };
            this.open = true;
        },
        addCred() {
            this.fields.credentials.push({ label: '', username: '', password: '', url: '' });
        },
        removeCred(i) {
            this.fields.credentials.splice(i, 1);
        },
        get action() {
            return this.editId ? `${config.updateBase}/${this.editId}` : config.storeUrl;
        },
    }));

    // Kanban: make each stage column sortable; on drop into another column,
    // tell Livewire to move the client to that stage.
    window.Alpine.data('kanban', () => ({
        init() {
            this.$el.querySelectorAll('[data-stage-column]').forEach((col) => {
                Sortable.create(col, {
                    group: 'pipeline',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    dragClass: 'rotate-1',
                    onEnd: (evt) => {
                        const stage = evt.to.getAttribute('data-stage-column');
                        const clientId = evt.item.getAttribute('data-client-id');
                        if (stage && clientId && evt.from !== evt.to) {
                            this.$wire.moveClient(parseInt(clientId), stage);
                        }
                    },
                });
            });
        },
    }));
});
