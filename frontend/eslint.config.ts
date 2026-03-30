import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import tseslint from 'typescript-eslint'
import globals from 'globals'

export default tseslint.config(
  // Globalne ignorowania
  {
    ignores: ['dist/**', 'node_modules/**', '*.config.js', 'public/**'],
  },

  // Bazowe reguły JS
  js.configs.recommended,

  // TypeScript
  ...tseslint.configs.recommended,

  // Vue 3
  ...pluginVue.configs['flat/recommended'],

  // Własna konfiguracja
  {
    files: ['**/*.vue', '**/*.ts'],
    languageOptions: {
      globals: {
        // Globals przeglądarki (setTimeout, clearTimeout, fetch, document, window…)
        ...globals.browser,
      },
      parserOptions: {
        parser: tseslint.parser,
        ecmaVersion: 'latest',
        sourceType: 'module',
        extraFileExtensions: ['.vue'],
      },
    },
    rules: {
      // TypeScript
      '@typescript-eslint/no-explicit-any': 'warn',
      '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
      '@typescript-eslint/explicit-function-return-type': 'off',
      '@typescript-eslint/no-non-null-assertion': 'warn',

      // Vue
      'vue/multi-word-component-names': 'off',
      'vue/no-unused-vars': 'warn',
      'vue/no-unused-components': 'warn',
      'vue/component-api-style': ['error', ['script-setup']],
      'vue/define-macros-order': ['error', {
        order: ['defineOptions', 'defineProps', 'defineEmits', 'defineSlots'],
      }],
      'vue/no-v-html': 'warn',
      'vue/require-default-prop': 'off',
      'vue/html-self-closing': ['error', {
        html: { void: 'always', normal: 'always', component: 'always' },
        svg: 'always',
        math: 'always',
      }],
      'vue/attributes-order': ['warn', { alphabetical: false }],
      'vue/order-in-components': 'error',
      'vue/padding-line-between-blocks': ['error', 'always'],

      // Wyłączamy valid-v-slot: Vuetify 3 używa slot-names z kropką (#item.name),
      // co jest poprawną składnią Vue 3 ale nie jest obsługiwane przez tę regułę.
      'vue/valid-v-slot': 'off',

      // Ogólne
      'no-console': ['warn', { allow: ['warn', 'error'] }],
      'prefer-const': 'error',
      'no-var': 'error',
      'eqeqeq': ['error', 'always'],
      'object-shorthand': ['error', 'always'],
    },
  },
)
