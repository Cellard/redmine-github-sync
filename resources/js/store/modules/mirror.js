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
    },
    setStartDate (ctx, payload) {
      ctx.commit('updateStartDate', payload);
    },
    setOwner (ctx, payload) {
      ctx.commit('updateOwner', payload);
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
    updateStartDate (state, value) {
      state.startDate = value;
    },
    updateOwner (state, value) {
      state.owner = value;
    },
  },
  state: {
    left: {},
    right: {},
    config: 'both',
    startDate: null,
    owner: null,
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
    },
    startDate (state) {
      return state.startDate;
    },
    owner (state) {
      return state.owner;
    }
  }
}