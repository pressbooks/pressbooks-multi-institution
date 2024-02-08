//import create_config from '@kucrut/vite-for-wp';

// export default create_config( {
// 	app: [
// 		'resources/assets/js/pressbooks-multi-institution.js',
// 		'resources/assets/js/pressbooks-multi-institution-users.js',
// 	],
// 	multiselect: 'node_modules/@pressbooks/multiselect/pressbooks-multiselect.js',
// }, 'dist' );

import {v4wp} from '@kucrut/vite-for-wp';

export default {
	plugins: [
		v4wp({
			input: {
				institutions: 'resources/assets/js/pressbooks-multi-institution.js',
				users: 'resources/assets/js/pressbooks-multi-institutions-users.js',
				multiselect: 'node_modules/@pressbooks/multiselect/pressbooks-multiselect.js',
			},
			outDir: 'dist',
		} ),
	]
}
