
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

// window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Vue.component('example-component', require('./components/ExampleComponent.vue'));

// const app = new Vue({
//     el: '#app'
// });

function ignoreProject(id, ignore = true) {
	var counterElement = document.getElementById('projectCounter');
	var count = parseInt(counterElement.textContent);
	
	var route = (ignore) ? 'ignore' : 'unignore';

	axios.patch('/' + route + '/' + id).then(function(response) {
		if (response?.status === 200) {
			document.getElementById('project_' + id).style.display = 'none';
			counterElement.innerHTML = (count-1);
		}
		else {
			alert('Sorry, something went wrong!');
		}
	});
}

global.ignoreProject = ignoreProject;