const path = require('path');

module.exports = {
  mode: 'development',
  entry: './src/js/app.js', // Entry point for JavaScript
  output: {
    filename: 'app.js', // Output file for JavaScript
    path: path.resolve(__dirname, 'dist'), // Output directory
  },
  module: {
    rules: [
      {
        test: /\.scss$/, // This rule applies to .scss files
        use: [
          'style-loader',   // Inject CSS into the DOM
          'css-loader',     // Converts CSS into CommonJS
          {
            loader: 'sass-loader', // Compiles Sass to CSS
            options: {
              api: 'modern-compiler', // This ensures the modern Sass API is used
              sassOptions: {
                // You can specify additional Sass options here (optional)
                // For example: outputStyle: 'compressed'
              },
            },
          },
        ],
      },
    ],
  },
  stats: {
    loggingDebug: ['sass-loader'], // Enable debug logging for sass-loader
  },
};
