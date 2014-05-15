module.exports = function(grunt) {
	// Project configuration
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		copy: {
			main: {
				files: [
					{
						expand: true,
						src: 'wordpress/**',
						dest: 'build/',
						cwd: 'www'
					},
					{
						expand: true,
						src: 'wp-content/*.php',
						dest: 'build/',
						cwd: 'www'
					},
					{
						expand: true,
						src: ['wp-content/mu-plugins/**','!/www/wp-content/mu-plugins/local-index.php'],
						dest: 'build/',
						cwd: 'www'
					},
					{
						expand: true,
						src: 'wp-content/plugins/index.php',
						dest: 'build/',
						cwd: 'www'
					},
					{
						expand: true,
						src: 'wp-content/themes/index.php',
						dest: 'build/',
						cwd: 'www'
					}
				]
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-copy');

	grunt.registerTask('default', ['copy']);
};