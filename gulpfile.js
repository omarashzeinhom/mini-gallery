'use strict';
const gulp = require('gulp');
const browserSync = require('browser-sync').create();

// Watch for changes and reload the browser
gulp.task(
    'watch', function () {
        browserSync.init(
            {
                host: "localhost/wordpress/",
                proxy: "localhost/wordpress/",
                notify: true,
                open: true,
            }
        );
        gulp.watch('.**/*.css').on('change', browserSync.reload); // Watch css files
        gulp.watch('**/*.js').on('change', browserSync.reload); // Watch JS files
        gulp.watch('**/*.php').on('change', browserSync.reload);// Watch Php files
    }
);

// Default task
gulp.task('default', gulp.series('watch'));