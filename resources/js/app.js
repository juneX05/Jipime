/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import {
    Form,
    HasError,
    AlertError,
    AlertErrors,
    AlertSuccess
} from 'vform';
import moment from 'moment';

window.Form = Form;
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);
Vue.component(AlertErrors.name, AlertErrors);
Vue.component(AlertSuccess.name, AlertSuccess);

import VueRouter from 'vue-router';
import VueProgressBar from 'vue-progressbar'
import swal from 'sweetalert2';
import router from './router';
import Gate from "../../modules/User/Vue/gate";

//protyping
Vue.prototype.$gate = new Gate(window.user);

window.swal = swal;

const toast = swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    onOpen: (toast) => {
        toast.addEventListener('mouseenter', swal.stopTimer);
        toast.addEventListener('mouseleave', swal.resumeTimer);
    }
});

window.toast = toast;

const progressbar_options = {
    color: 'green',
    failedColor: 'red',
    thickness: '5px',
    autoRevert: true,
    location: 'top',
    inverse: false
};

Vue.use(VueRouter);
Vue.use(VueProgressBar, progressbar_options);

//register a global filter
Vue.filter('upText', function(text){
    return text.charAt(0).toUpperCase() + text.slice(1);
});

Vue.filter('myDate',function (date) {
    return moment(date).format('MMMM Do YYYY');
});

window.Fire = new Vue();

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);

Vue.component('not-found', require("./components/NotFound.vue").default);
Vue.component('pagination', require("laravel-vue-pagination"));
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router,
    data: {
        search:'',
        userimage:'./img/profile.png',
        username:'User',
        usertype:'user',
    },
    methods:{
        searchIt(){
            Fire.$emit('Searching');
        },
        searchInASec:_.debounce(() => {
            Fire.$emit('Searching');
        },1000),
        userUpdate(){
            if (window.user && window.user.photo !== ''){
                this.userimage = '/img/profile/'+window.user.photo;
                this.username = window.user.name;
                this.usertype = window.user.type;
            }
        },
        shuffler(items){
            return items
                .map((a) => ({sort: Math.random(), value: a}))
                .sort((a, b) => a.sort - b.sort)
                .map((a) => a.value)

        }
    },
    created(){
        this.userUpdate()
    }
});