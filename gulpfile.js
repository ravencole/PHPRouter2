var gulp = require('gulp');
var phpunit = require('gulp-phpunit');

gulp.task('test', function() {
    gulp.src(['tests/**/*.php'])
        .pipe(phpunit('phpunit', { notify: true, clear: true }))
});

gulp.task('watch', function() {
    gulp.watch(['Router/**/*.php', 'tests/**/*.php'], ['test']);
});

gulp.task('tdd', ['test', 'watch']);
