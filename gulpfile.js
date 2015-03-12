// indEE Gulpfile
// (c) Blue State Digital

// TASKS
// ------
// `gulp`: watch, compile styles and scripts; LiveReload
// `gulp build`: default compile task


// PLUGINS
// --------
var gulp = require('gulp'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-minify-css'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    livereload = require('gulp-livereload'),
    p = require('./package.json');


// REPOSITORY NAME
// ----------------
// Important for deploy paths: You MUST update the 'repository' object's
// name in package.json, or bad things will happen.

var REPO_NAME = p.repository.name;


// VARIABLES
// ----------
var jssource = 'src/';


// ERROR HANDLING
// ---------------
function handleError() {
  this.emit('end');
}


// BUILD SUBTASKS
// ---------------

gulp.task('styles', function() {
  gulp.src('scss/styles.scss')
    .pipe(sass())
      .on('error', handleError)
      .on('error', notify.onError())
    .pipe(autoprefixer('last 3 versions'))
    .pipe(minifycss())
    .pipe(gulp.dest('css'));
});

// Script Linter
gulp.task('lint', function() {
  return gulp.src([
      jssource+'js/editor.js',
    ])
    .pipe(jshint('.jshintrc'))
    .pipe(jshint.reporter('default'));
});

// Scripts
gulp.task('scripts', ['lint'], function() {
  return gulp.src([
      jssource+'lib/underscore.js',
      jssource+'editor.js'
    ])
    .pipe(concat('editor.dev.js'))
    .pipe(rename('editor.js'))
    .pipe(uglify())
      .on('error', handleError)
      .on('error', notify.onError())
    .pipe(gulp.dest('js'));
});

// BUILD TASKS
// ------------

// Watch
gulp.task('default', ['build'], function() {

  // Initialize LiveReload server
  livereload.listen();

  // Watch .scss files
  gulp.watch('scss/**/*.scss', ['styles']);

  // Watch .js files
  gulp.watch(jssource+'/**/*.js', ['scripts']);

  // Watch templates, JS, and CSS, reload on change
  gulp.watch([
      '**/*.php',
      '**/*.html',
      'css/*.css',
      'js/**/*.js'
    ], { dot: true })
    .on('change', livereload.changed);

});

// Build
gulp.task('build', function() {
    gulp.start('styles', 'scripts');
});
