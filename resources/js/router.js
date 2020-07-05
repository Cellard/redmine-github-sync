import Vue from 'vue'
import VueRouter from 'vue-router'
import { store } from './store'

Vue.use(VueRouter);

export const routes = [
  {
    path: '/',
    name: 'logs.index',
    component: () => import('./views/Logs')
  },
  {
    path: '/servers',
    name: 'servers.index',
    component: () => import('./views/Servers')
  },
  {
    path: '/mirrors',
    name: 'mirrors.index',
    component: () => import('./views/Mirrors')
  }
];

const router = new VueRouter({
  mode: 'history',
  routes,
});

router.beforeEach((to, from, next) => {
  store.dispatch('initLoading');
  next();
})

export default router;