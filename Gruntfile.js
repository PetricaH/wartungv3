module.exports = function(grunt) {
    // Project configuration
    grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),
  
      // Concatenate JavaScript files
      concat: {
        dist: {
          src: ['static/js/*.js'], // Source files
          dest: 'static/js/bundle.js' // Destination file
        }
      },
  
      // Minify the concatenated JavaScript file
      uglify: {
        dist: {
          files: {
            'static/js/bundle.min.js': ['static/js/bundle.js'] // Minified output file
          }
        }
      },
  
      // Watch for changes and run tasks
      watch: {
        scripts: {
          files: ['static/js/*.js'], // Files to watch
          tasks: ['concat', 'uglify'], // Tasks to run on change
          options: {
            spawn: false,
          },
        },
      }
    });
  
    // Load the plugins
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
  
    // Default task(s)
    grunt.registerTask('default', ['concat', 'uglify']);
  };
  