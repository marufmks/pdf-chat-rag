const defaults = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaults,
	entry: {
		admin: './assets/src/admin/index.js',
		frontend: './assets/src/frontend/index.js',
		shortcode: './assets/src/shortcode/index.js',
	},
};
