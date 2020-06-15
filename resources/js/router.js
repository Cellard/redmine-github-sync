import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter);

export const routes = [
    {path: '/servers', name: 'servers.index', component: () => import('./components/Servers')},
    {path: '/servers/create', name: 'servers.create', component: () => import('./components/Server')},
    {path: '/servers/:id', name: 'servers.edit', component: () => import('./components/Server')},
];

const router = new VueRouter({
    mode: 'history',
    base: 'home',
    routes,
});

export default router;
