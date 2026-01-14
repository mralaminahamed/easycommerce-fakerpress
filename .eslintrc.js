module.exports = {
  extends: [
    "plugin:@wordpress/eslint-plugin/recommended",
    "plugin:@wordpress/eslint-plugin/esnext",
    "plugin:@wordpress/eslint-plugin/i18n",
    "plugin:@wordpress/eslint-plugin/react",
  ],
  env: {
    browser: true,
    es6: true,
    node: true,
    jquery: true,
  },
  globals: {
    wp: "readonly",
    easycommerceFakerpressApi: "readonly",
    ajaxurl: "readonly",
    console: "readonly",
  },
  parserOptions: {
    ecmaVersion: 2020,
    sourceType: "module",
  },
  settings: {
    "import/resolver": {
      alias: {
        map: [["@", "./src"]],
        extensions: [".ts", ".tsx", ".js", ".jsx", ".json"],
      },
    },
  },
  rules: {
    // Custom rules for this project
    "no-console": "warn",
    "no-debugger": "error",
    "prefer-const": "error",
    "no-var": "error",
    "object-shorthand": "error",
    "prefer-arrow-callback": "error",
    "arrow-spacing": "error",
    "prefer-template": "error",

    // Import order enforcement
    "import/order": [
      "error",
      {
        groups: [
          "external", // External libraries (React, third-party)
          "builtin", // Node.js built-ins
          "internal", // WordPress packages (@wordpress/*)
          "parent", // Parent directories
          "sibling", // Same level
          "index", // Index files
        ],
        pathGroups: [
          {
            pattern: "@wordpress/**",
            group: "internal",
            position: "before",
          },
          {
            pattern: "@/**",
            group: "parent",
            position: "after",
          },
        ],
        pathGroupsExcludedImportTypes: ["builtin"],
        alphabetize: {
          order: "asc",
          caseInsensitive: true,
        },
        "newlines-between": "always",
      },
    ],

    // Enforce consistent use of single quotes
    "prettier/prettier": "off",

    // WordPress specific
    "@wordpress/no-unused-vars-before-return": "error",
    "@wordpress/valid-sprintf": "error",
    "@wordpress/i18n-text-domain": [
      "error",
      {
        allowedTextDomain: "easycommerce-fakerpress",
      },
    ],
    "@wordpress/i18n-translator-comments": "error",
    "@wordpress/i18n-no-variables": "error",
    "@wordpress/i18n-no-placeholders-only": "error",
    "@wordpress/i18n-ellipsis": "error",
  },
  overrides: [
    {
      files: ["**/*.ts", "**/*.tsx"],
      parser: "@typescript-eslint/parser",
      parserOptions: {
        project: true,
      },
      extends: [
        "plugin:@typescript-eslint/recommended",
        "plugin:@typescript-eslint/recommended-requiring-type-checking",
      ],
      rules: {
        "@typescript-eslint/no-unused-vars": "error",
        "@typescript-eslint/explicit-function-return-type": "off",
        "@typescript-eslint/explicit-module-boundary-types": "off",
        "@typescript-eslint/no-explicit-any": "warn",
      },
    },
    {
      files: ["**/*.test.js", "**/*.spec.js", "**/*.test.ts", "**/*.spec.ts"],
      env: {
        jest: true,
      },
    },
  ],
};
