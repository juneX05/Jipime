import VueRouter from 'vue-router'
//import your routes here
import users_routes from "../../modules/User/Vue/routes";

const startRoutes = [
    //add routes to be placed at the beginning
    //{path: '*', component:require('./components/NotFound.vue').default},
];

const endRoutes = [
    //add routes to be placed at the end.
    {path: '*', component:require('./components/NotFound.vue').default},
];

let moduleRoutes = [];
moduleRoutes = moduleRoutes.concat(
    //add your module routes in here
    users_routes,
);

const routes = startRoutes.concat(
    moduleRoutes,
    endRoutes
);

const router = new VueRouter({
    history: true,
    mode: 'history',
    linkActiveClass: "active",
    linkExactActiveClass: "active",
    routes
});

export default router