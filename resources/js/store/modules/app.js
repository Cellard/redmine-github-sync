export default {
  actions: {
    initLoading (ctx) {
      ctx.commit('updateLoading', true);
    },
    finishLoading (ctx) {
      ctx.commit('updateLoading', false);
    }
  },
  mutations: {
    updateLoading (state, isLoading) {
      state.loading = isLoading;
    }
  },
  state: {
    loading: false
  },
  getters: {
    loading (state) {
      return state.loading;
    }
  }
}