import Vue from 'vue';
import Vuex from 'vuex';
import app from './modules/app';
import drawler from './modules/drawler';

Vue.use(Vuex)

export const store = new Vuex.Store({
  modules: {
    app,
    drawler
  }
});