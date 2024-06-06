/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window.Vue = require('vue');
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('style-builder-component',require('./components/Admin/Item/StyleBuilder').default);
Vue.component('topic-builder-item-component',require('./components/Admin/Item/TopicBuilder').default);
Vue.component('topic-builder-component',require('./components/Admin/Topic/Create/TopicBuilder').default);
Vue.component('topic-builder-edit-component',require('./components/Admin/Topic/Edit/TopicBuilder').default);
Vue.component('style-builder-task-component',require('./components/Admin/Task/StyleBuilder').default);
Vue.component('type-builder-task-component',require('./components/Admin/Task/TypeBuilder').default);
Vue.component('type-builder-topic-component',require('./components/Admin/Topic/TypeBuilder').default);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: 'main',
});
