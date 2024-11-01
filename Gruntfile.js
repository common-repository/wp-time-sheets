module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    uglify: {
      options: {
        sourceMap: false,
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n',
        compress: {
          drop_console: false
        },
        beautify: true,
        mangle: false,
        sourceMap : false
      },
      public: {
        files: {
          'public/js/wp-timesheets-public.min.js':
          [
            'public/js/vendor/timesheet.js',
            'public/js/main.js'
          ]
        }
      },
      admin: {
        files: {
          'admin/js/wp-timesheets-admin.min.js':
          [
            'admin/js/vendor/jquery.validate.js',
            'admin/js/vendor/datepicker.js',
            'admin/js/vendor/datepicker.en.js',
            'admin/js/vendor/jquery.magnific-popup.js',
            'admin/js/main.js'
          ]
        }
      }
    },
    cssmin: {
      options: {
        mergeIntoShorthands: false,
        roundingPrecision: -1
      },
      admin: {
        files: {
          'admin/css/wp-timesheets-admin.min.css': [
             'admin/css/datepicker.css',
             'admin/css/magnific-popup.css',
             'admin/css/main.css'
          ]
        }
      },
      public: {
        files: {
          'public/css/wp-timesheets-public.min.css': [
             'public/css/timesheet.css',
             'public/css/timesheet-white.css'
          ]
        }
      }
    }

  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-cssmin');

  // Default task(s).
  grunt.registerTask('default', ['uglify', 'cssmin']);

};
