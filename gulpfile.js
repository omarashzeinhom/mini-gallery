'use strict';
const gulp = require('gulp');
const browserSync = require('browser-sync').create();

// Watch for changes and reload the browser
gulp.task('watch', function () {
  browserSync.init({
    host: "localhost/wpreact",
    proxy: "localhost/wpreact",
    notify: true,
  });
  //TODO ADD LOADING STYLES FOR EACH PAGE TO INCREASE PERFORMANCE.
  gulp.watch('.**/*.css', gulp.series('styles')).on('change', browserSync.reload);
  gulp.watch('**/*.js', gulp.series('scripts')).on('change', browserSync.reload); // Watch JS files
  gulp.watch('**/*.php').on('change', browserSync.reload);
});

// Default task
gulp.task('default', gulp.series('styles', 'scripts', 'watch'));