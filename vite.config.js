import create_config from '@kucrut/vite-for-wp';

export default create_config({
	app: 'resources/assets/js/pressbooks-multi-institution.js',
	multiselect: 'node_modules/@pressbooks/multiselect/pressbooks-multiselect.js',
}, 'dist' );
