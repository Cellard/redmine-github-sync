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
      state[data.direction + 'LabelsMap'] = data.value;
    },
  },
  state: {
    left: {},
    right: {},
    ltrLabelsMap: [{
      id: 1,
      left_label_id: '',
      right_label_id: ''
    }],
    rtlLabelsMap: [{
      id: 1,
      left_label_id: '',
      right_label_id: ''
    }]
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
    }
  }
}