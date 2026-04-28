import wordpress from '@wordpress/eslint-plugin';
import globals from 'globals';

export default [
	...wordpress.configs.recommended,
	...wordpress.configs.esnext,
	...wordpress.configs.i18n,
	...wordpress.configs.react,

	{
		languageOptions: {
			ecmaVersion: 2020,
			sourceType: 'module',
			globals: {
				...globals.browser,
				...globals.es2015,
				...globals.node,
				...globals.jquery,
				wp: 'readonly',
				easycommerceFakerpressApi: 'readonly',
				ajaxurl: 'readonly',
			},
		},
		settings: {
			'import/resolver': {
				alias: {
					map: [ [ '@', './src' ] ],
					extensions: [ '.ts', '.tsx', '.js', '.jsx', '.json' ],
				},
			},
		},
		rules: {
			'no-console': 'warn',
			'no-debugger': 'error',
			'prefer-const': 'error',
			'no-var': 'error',
			'object-shorthand': 'error',
			'prefer-arrow-callback': 'error',
			'arrow-spacing': 'error',
			'prefer-template': 'error',
			'import/order': [
				'error',
				{
					groups: [
						'external',
						'builtin',
						'internal',
						'parent',
						'sibling',
						'index',
					],
					pathGroups: [
						{
							pattern: '@wordpress/**',
							group: 'internal',
							position: 'before',
						},
						{
							pattern: '@/**',
							group: 'parent',
							position: 'after',
						},
					],
					pathGroupsExcludedImportTypes: [ 'builtin' ],
					alphabetize: {
						order: 'asc',
						caseInsensitive: true,
					},
					'newlines-between': 'always',
				},
			],
			'prettier/prettier': 'off',
			'@wordpress/no-unused-vars-before-return': 'error',
			'@wordpress/valid-sprintf': 'error',
			'@wordpress/i18n-text-domain': [
				'error',
				{ allowedTextDomain: 'easycommerce-fakerpress' },
			],
			'@wordpress/i18n-translator-comments': 'error',
			'@wordpress/i18n-no-variables': 'error',
			'@wordpress/i18n-no-placeholders-only': 'error',
			'@wordpress/i18n-ellipsis': 'error',
		},
	},

	{
		files: [ '**/*.ts', '**/*.tsx' ],
		languageOptions: {
			parserOptions: { project: true },
		},
		rules: {
			'@typescript-eslint/no-unused-vars': 'error',
			'@typescript-eslint/explicit-function-return-type': 'off',
			'@typescript-eslint/explicit-module-boundary-types': 'off',
			'@typescript-eslint/no-explicit-any': 'warn',
		},
	},

	{
		files: [ '**/*.test.{js,ts}', '**/*.spec.{js,ts}' ],
		languageOptions: {
			globals: { ...globals.jest },
		},
	},
];
