<template>
  <el-form :inline="true" ref="mirrorForm" label-position="top">
    <div :key="label.id" v-for="label in labelsMap">
      <el-form-item :label="mirrorDirection === 'ltr' ? 'Left label' : 'Right label'">
        <el-cascader
          @change="onLabelChange"
          :props="{ expandTrigger: 'hover', emitPath: false }"
          v-model="label.left_label_id"
          :options="left">
        </el-cascader>
      </el-form-item>
      <el-form-item :label="mirrorDirection === 'ltr' ? 'Right label' : 'Left label'">
        <el-cascader
          @change="onLabelChange"
          :props="{ expandTrigger: 'hover', emitPath: false }"
          v-model="label.right_label_id"
          :options="right">
        </el-cascader>
      </el-form-item>
      <el-form-item style="vertical-align: bottom">
        <el-button @click="removeRow(label)" type="danger" icon="el-icon-delete" circle></el-button>
      </el-form-item>
    </div>
    <el-button @click="addRow" type="primary" icon="el-icon-circle-plus" round>Add</el-button>
  </el-form>
</template>

<script>
  import { store } from '../store'
  import { mapGetters } from 'vuex';
  import { Message } from 'element-ui';

  export default {
    props: ['mirrorDirection'],
    data() {
      return {
        loading: true,
        direction: 'rtl',
        left: [],
        right: [],
        labelsMap: [{
          id: 1,
          left_label_id: '',
          right_label_id: ''
        }]
      };
    },
    computed: {
      ...mapGetters(['ltrMirrorLabels', 'rtlMirrorLabels', 'leftMirror', 'rightMirror']),
      mirrorLabels: function () {
        return this.mirrorDirection === 'ltr' ? this.ltrMirrorLabels : this.rtlMirrorLabels;
      }
    },
    watch: {
      async leftMirror() {
        this.setLabels();
      },
      async rightMirror() {
        this.setLabels();
      },
      async mirrorLabels(newValue) {
        if (newValue.length)
          this.labelsMap = newValue;
        else
          this.labelsMap = [{
            id: 1,
            left_label_id: '',
            right_label_id: ''
          }];
      },
      left () {
        this.disableChecked();
      }
    },
    methods: {
      fetchLabels: function (id) {
        return this.$http.get('/api/servers/' + id + '/labels')
          .then(response => {
            return response.data.data;
        });
      },
      setLabels: async function () {
        this.labelsMap = [{
            id: 1,
            left_label_id: '',
            right_label_id: ''
          }];
        this.left = [];
        this.right = [];
        if (this.leftMirror.server && this.rightMirror.server) {
          this.left = await this.fetchLabels(this.mirrorDirection === 'ltr' ? this.leftMirror.server : this.rightMirror.server);
          this.right = await this.fetchLabels(this.mirrorDirection === 'ltr' ? this.rightMirror.server : this.leftMirror.server);
        }
      },
      onLabelChange: function (value) {
        this.disableChecked(value[value.length - 1]);
        store.dispatch('setLabels', {direction: this.mirrorDirection, value: this.labelsMap});
      },
      disableChecked: function (checkedValue = null) {
        this.left.forEach(value => {
          value.children = value.children.map((item, index) => {
            if (this.labelsMap.some(e => e.left_label_id === item.value) || checkedValue === item.value) {
              return {
                ...item,
                disabled: true
              }
            } else {
              return {
                ...item,
                disabled: false
              }
            }
          });
        });
      },
      addRow() {
        this.labelsMap.push({
          id: Date.now(),
          left_label_id: '',
          right_label_id: ''
        });
      },
      removeRow(item) {
        var index = this.labelsMap.indexOf(item);
        if (this.labelsMap.length !== 1) {
          this.labelsMap.splice(index, 1);
          this.disableChecked();
        }
      },
    },
    mounted: async function () {
      this.setLabels();
    }
  };
</script>
