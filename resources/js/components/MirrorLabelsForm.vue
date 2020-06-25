<template>
  <el-form :inline="true" ref="mirrorForm" label-position="top">
    <div :key="label.id" v-for="label in labelsMap">
      <el-form-item label="Left label">
        <el-select @change="onLabelChange" v-model="label.left_label_id" placeholder="Select">
          <el-option
            v-for="item in left"
            :key="item.id"
            :label="item.name"
            :value="item.id">
          </el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="Right label">
        <el-select @change="onLabelChange" v-model="label.right_label_id" placeholder="Select">
          <el-option
            v-for="item in right"
            :key="item.id"
            :label="item.name"
            :value="item.id">
          </el-option>
        </el-select>
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
      ...mapGetters(['mirrorLabels', 'leftMirror', 'rightMirror'])
    },
    watch: {
      async leftMirror(newValue) {
        if (newValue.server)
          this.left = await this.fetchLabels(newValue.server);
      },
      async rightMirror(newValue) {
        if (newValue.server)
          this.right = await this.fetchLabels(newValue.server);
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
    },
    methods: {
      fetchLabels: function (id) {
        return this.$http.get('/api/servers/' + id + '/labels')
          .then(response => {
            return response.data.data;
        });
      },
      onLabelChange: function () {
        store.dispatch('setLabels', this.labelsMap);
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
        }
      },
    },
    mounted: async function () {
      if (this.leftMirror.server)
        this.left = await this.fetchLabels(this.leftMirror.server);
      if (this.rightMirror.server)
        this.right = await this.fetchLabels(this.rightMirror.server);
    }
  };
</script>
