const livereload = require('livereload');
const chalk = require('chalk'); // Import chalk for colorful text

const server = livereload.createServer({
    // port: 5000, // Default Port is 35729 Change this to your desired port number
});

server.watch(__dirname);

// Define port numbers
const livereloadPort = server.config.port;
const browserSyncPort = 3000; // Change this to your desired port number

// Output messages with chalk colors and styles
console.log(chalk.yellow.bold('🔥 Livereload server is watching for changes 🔍'));
console.log(chalk.cyan.bold('-----------------------------------------------'));
console.log(chalk.green.bold(`  Livereload: https://localhost:${livereloadPort}`));
console.log(chalk.green.bold(`  BrowserSync: https://localhost:${browserSyncPort}/wpreact`));
console.log(chalk.cyan.bold('-----------------------------------------------'));
console.log(chalk.blue.bold('UI: https://localhost:3001'));
console.log(chalk.blue.bold('UI External: https://192.168.56.1:3001'));
console.log(chalk.cyan.bold('-----------------------------------------------'));
