export default {
  actions: {
    setMirror (ctx, payload) {
      ctx.commit('updateMirror', payload);
    },
    setLabels (ctx, payload) {
      ctx.commit('updateLabels', payload);
    },
    setConfig (ctx, payload) {
      ctx.commit('updateConfig', payload);
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
      state[data.direction + 'LabelsMap'] = data.value;
    },
    updateConfig (state, value) {
      state.config = value;
    },
  },
  state: {
    left: {},
    right: {},
    config: 'both',
    ltrLabelsMap: [],
    rtlLabelsMap: []
  },
  getters: {
    leftMirror (state) {
      return state.left;
    },
    rightMirror (state) {
      return state.right;
    },
    ltrMirrorLabels (state) {
      return state.ltrLabelsMap;
    },
    rtlMirrorLabels (state) {
      return state.rtlLabelsMap;
    },
    config (state) {
      return state.config;
    }
  }
}