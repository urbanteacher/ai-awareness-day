/**
 * wp-scripts' default config, plus: the @wordpress/* packages are provided by WordPress at runtime (webpack externals),
 * not installed here, so the import rules must not flag them.
 */
const defaults = require( '@wordpress/scripts/config/eslint.config.cjs' );

module.exports = [
	...defaults,
	{
		rules: {
			'import/no-unresolved': [ 'error', { ignore: [ '^@wordpress/' ] } ],
			// Same reason: they are externals, so they are not listed in package.json.
			'import/no-extraneous-dependencies': 'off',
		},
	},
];
