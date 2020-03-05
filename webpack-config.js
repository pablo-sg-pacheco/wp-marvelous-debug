const path = require('path')
	fs = require("fs"),
	UglifyJSPlugin = require('uglifyjs-webpack-plugin');

/*
const devConfig = {
	context: __dirname,
	entry: {
		frontend: ['./src/assets/js/frontend/frontend-index.js', './src/assets/scss/frontend/frontend.scss'],
		admin: ['./src/assets/js/admin/admin-index.js', './src/assets/scss/admin/admin.scss']
*/

// Development
const devConfig = {
	context: __dirname,
	entry: {
		general: ['./src/assets/js/general/general-index.js']
	},
	output: {
		path: path.resolve(__dirname, 'assets'),
		filename: '[name].js'
	},
	mode: 'development',
	devtool: 'source-map',
	module: {
		rules: [
			{
				enforce: 'pre',
				exclude: /node_modules/,
				test: /\.jsx?$/,
				loader: 'eslint-loader',
				options: {
					fix: true,
				},
			},
			{
				exclude: /node_modules/,
				test: /\.jsx?$/,
				loader: 'babel-loader',
				options: {
					presets: ["@babel/preset-env"]
				}
			}
		]
	},
	optimization: {
		minimize: false
	}
};

// Production
const prodConfig = {
	...devConfig,
	mode: 'production',
	output: {
		path: path.resolve(__dirname, 'assets'),
		filename: '[name].min.js'
	},
	optimization: {
		minimizer: [new UglifyJSPlugin({
			uglifyOptions: {
				output: { // See https://github.com/mishoo/UglifyJS2#output-options
					beautify: false,
					comments: 'some',
				},
			}
		})]
	}
}

module.exports = (env, argv) => {
	switch (argv.mode) {
		case 'production':
			return prodConfig;
		default:
			return devConfig;
	}
}