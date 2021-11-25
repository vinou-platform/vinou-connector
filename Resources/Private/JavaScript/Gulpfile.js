var gulp = require('gulp');
var watch = require('gulp-watch');
var terser = require('gulp-terser');
var optimisejs = require('gulp-optimize-js');
var stripDebug = require('gulp-strip-debug');
var gulpUtil = require('gulp-util');
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var watchDirs = ['Src/**/*'];
var vinouJsFiles = [
	'./Src/shop.js',
	'./Src/shopMarketplace.js',
	'./Src/list.js'
];


gulp.task('minify', function() {
	return gulp.src('./Src/*.js')
		.pipe(gulp.dest('../../Public/Scripts'))
		.pipe(stripDebug())
		.pipe(optimisejs())
		.pipe(terser().on('error', gulpUtil.log))
	    .pipe(rename({
            suffix: '.min'
        }))
		.pipe(gulp.dest('../../Public/Scripts'))
		.on('error', function (error) {
			console.error('' + error);
		});
});

gulp.task('concat', function() {
	return gulp.src(vinouJsFiles)
	    .pipe(concat('vinou.js'))
		.pipe(gulp.dest('../../Public/Scripts'))
		.pipe(stripDebug())
		.pipe(optimisejs())
		.pipe(terser().on('error', gulpUtil.log))
	    .pipe(rename({
            suffix: '.min'
        }))
		.pipe(gulp.dest('../../Public/Scripts'))
		.on('error', function (error) {
			console.error('' + error);
		});
});

gulp.task('watch', function() {
	return watch(watchDirs, gulp.series('concat', 'minify'));
});

gulp.task('default', gulp.series('concat', 'minify', 'watch'));
