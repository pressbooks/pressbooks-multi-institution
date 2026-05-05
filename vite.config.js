import { createWpViteConfig } from 'pressbooks-build-tools';
import { resolve } from 'path';

export default createWpViteConfig({
	input: {
		app: resolve(__dirname, 'resources/assets/js/pressbooks-multi-institution.js'),
		multiselect: resolve(__dirname, 'node_modules/@pressbooks/select/pressbooks-select.js'),
	},
	outDir: 'assets/dist',
});
