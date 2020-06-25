export default {
  actions: {
    setMirror (ctx, payload) {
      ctx.commit('updateMirror', payload);
    },
    setLabels (ctx, payload) {
      ctx.commit('updateLabels', payload);
    }
  },
  mutations: {
    updateMirror (state, data) {
      const currentState = state[data.position];
      state[data.position] = {
        ...currentState,
        ...data.value
      };
    },
    updateLabels (state, data) {
      state.labels = data;
    },
  },
  state: {
    left: {},
    right: {},
    labels: []
  },
  getters: {
    leftMirror (state) {
      return state.left;
    },
    rightMirror (state) {
      return state.right;
    },
    mirrorLabels (state) {
      return state.labels;
    }
  }
}