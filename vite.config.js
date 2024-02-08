import create_config from '@kucrut/vite-for-wp';

export default create_config({
	institution: 'resources/assets/js/pressbooks-multi-institution.js',
	user: 'resources/assets/js/pressbooks-multi-institutions-users.js',
	multiselect: 'node_modules/@pressbooks/multiselect/pressbooks-multiselect.js',
}, 'dist' );
