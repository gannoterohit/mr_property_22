import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('staffForm', () => ({
	open: false,
	edit: null,
	resetForm() {
		this.open = true;
		this.edit = null;
	},
	editStaff(payload) {
		this.edit = payload;
		this.open = true;
	},
}));

Alpine.start();
