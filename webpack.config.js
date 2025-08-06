const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		admin: './src/admin/index.js',
	},
	output: {
		path: path.resolve( __dirname, 'build' ),
		filename: '[name].js',
		clean: true,
	},
	performance: {
		hints: false,
		maxEntrypointSize: 512000,
		maxAssetSize: 512000,
	},
};
