import {v4wp} from '@kucrut/vite-for-wp';
export default {
	plugins: [
		v4wp(
			{
				input: {
					app: 'resources/assets/js/pressbooks-multi-institution.js',
					multiselect: 'node_modules/@pressbooks/select/pressbooks-select.js',
				},
				outDir: 'dist'
			}
		)
	]
}
