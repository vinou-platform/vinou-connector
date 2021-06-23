var gulp = require('gulp');
var watch = require('gulp-watch');
var sass = require('gulp-sass');
var prefix = require('gulp-autoprefixer');
var minifyCSS = require('gulp-minify-css');
var paths = {
	watch: ['./Assets/*', './Assets/**/*', './Assets/**/**/*', './Lib/*']

}

gulp.task('sass', function(){
	return gulp.src('./Assets/*.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(prefix('last 5 versions', 'Chrome 30', 'Firefox ESR', 'Opera 12.1', 'iOS 6','IE 8'))
		.pipe(minifyCSS({
			compatibility: 'ie8',
			rebase: false
		}))
		.pipe(gulp.dest('../../Public/Styles'));
});

gulp.task('watch', function() {
	return watch(paths.watch, gulp.series('sass'));
});

gulp.task('default', gulp.series('sass', 'watch'));