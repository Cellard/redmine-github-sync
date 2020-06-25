export default {
  actions: {
    show (ctx, payload) {
      ctx.commit('updateData', payload);
      ctx.commit('updateVisibility', true);
    },
    close (ctx) {
      ctx.commit('updateVisibility', false);
      ctx.commit('updateData', null);
    }
  },
  mutations: {
    updateVisibility (state, isVisible) {
      state.isVisible = isVisible;
    },
    updateData (state, data) {
      state.data = data;
    }
  },
  state: {
    isVisible: false,
    data: null
  },
  getters: {
    drawlerVisibility (state) {
      return state.isVisible;
    },
    drawlerData (state) {
      return state.data;
    }
  }
}