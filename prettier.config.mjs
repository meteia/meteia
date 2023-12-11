export default {
  "plugins": ["@prettier/plugin-php"],
  "singleQuote": true,
  "jsxSingleQuote": true,
  "tabWidth": 2,
  "overrides": [
    {
      "files": "*.php",
      "options": {
        "phpVersion": "8.2",
        "tabWidth": 4,
        "printWidth": 120,
        "singleQuote": true,
      }
    }
  ]
};
