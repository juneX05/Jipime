const users_routes = [
    {path: '/home', component:require('./components/Dashboard.vue').default},
    {path: '/profile', component:require('./components/Profile.vue').default},
    {path: '/users', component:require('./components/Users.vue').default},

];

export default users_routes