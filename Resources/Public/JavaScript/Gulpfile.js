var gulp = require('gulp');
var terser = require('gulp-terser');
var optimisejs = require('gulp-optimize-js');
var stripDebug = require('gulp-strip-debug');
var gulpUtil = require('gulp-util');
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var watchDirs = ['Src/**/*'];
var vinouJsFiles = ['Src/enquiry.js','Src/shop.js','Src/list.js'];


gulp.task('vinou-js', function () {
	return gulp.src(vinouJsFiles)
		.pipe(optimisejs())
	    .pipe(concat('vinou.js'))
		.pipe(gulp.dest('Src'))
		.on('error', function (error) {
			console.error('' + error);
		});
});

gulp.task('minify', ['vinou-js'], function () {
	return gulp.src('./Src/*.js')
		.pipe(stripDebug())
		.pipe(optimisejs())
		.pipe(terser().on('error', gulpUtil.log))
	    .pipe(rename({
            suffix: '.min'
        }))
		.pipe(gulp.dest('Minified'))
		.on('error', function (error) {
			console.error('' + error);
		});
});

gulp.task('default', ['vinou-js'], function () {
	gulp.watch(watchDirs, ['vinou-js']);
});