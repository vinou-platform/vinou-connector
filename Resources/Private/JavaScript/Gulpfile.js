var gulp = require('gulp');
var terser = require('gulp-terser');
var optimisejs = require('gulp-optimize-js');
var stripDebug = require('gulp-strip-debug');
var gulpUtil = require('gulp-util');
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var watchDirs = ['Src/**/*'];
var vinouJsFiles = [
	'../../Public/Scripts/shop.min.js',
	'../../Public/Scripts/list.min.js'
];


gulp.task('minify', (done) => {
	gulp.src('./Src/*.js')
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

	done();
});

gulp.task('concat', gulp.series('minify', (done) => {
	gulp.src(vinouJsFiles)
		.pipe(optimisejs())
	    .pipe(concat('vinou.min.js'))
		.pipe(gulp.dest('../../Public/Scripts'))
		.on('error', function (error) {
			console.error('' + error);
		});

	done();
}));



gulp.task('watch', function() {
	gulp.watch(watchDirs, ['concat']);
});

gulp.task('default', gulp.series('concat', (done) => {
	gulp.watch(watchDirs, gulp.series('concat'));
	done();
}));