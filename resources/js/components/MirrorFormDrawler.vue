<template>
  <div>

    <el-button @click="show()" style="margin-bottom: 10px" type="primary" icon="el-icon-circle-plus">Add mirror</el-button>

    <el-drawer
      :title="drawlerData ? leftMirror.server + ' - ' + rightMirror.server : 'New Mirror'"
      :visible.sync="drawlerVisibility"
      :direction="direction"
      ref="drawer">
      <div v-loading="loading" class="drawer__content">
        <el-divider content-position="left">
          <h4>Left</h4>
        </el-divider>
        <mirror-project-form position="left"></mirror-project-form>

        <el-divider content-position="left">
          <h4>Right</h4>
        </el-divider>
        <mirror-project-form  position="right"></mirror-project-form>

        <el-divider content-position="left">
          <h4>Config</h4>
        </el-divider>
        <el-form :inline="true" label-position="top">
          <el-form-item label="Sync direction">
            <el-select @change="onConfigChange" :value="config" placeholder="Select">
              <el-option
                key="both"
                label="Both"
                value="both">
              </el-option>
              <el-option
                key="ltr"
                label="From left to right"
                value="ltr">
              </el-option>
              <el-option
                key="rtl"
                label="From right to left"
                value="rtl">
              </el-option>
            </el-select>
          </el-form-item>
        </el-form>

        <el-divider content-position="left">
          <h4>Left to Right Labels</h4>
        </el-divider>
        <mirror-labels-form mirrorDirection="ltr"></mirror-labels-form>

        <el-divider content-position="left">
          <h4>Right to Left Labels</h4>
        </el-divider>
        <mirror-labels-form mirrorDirection="rtl"></mirror-labels-form>

        <el-divider></el-divider>
        <div class="demo-drawer__footer">
          <el-button @click="close()">Cancel</el-button>
          <el-button type="primary" @click="submit()" :loading="formLoading">{{ formLoading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>

  </div>
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
        formLoading: false,
      };
    },
    computed: {
      ...mapGetters([
        'drawlerVisibility', 
        'drawlerData', 
        'leftMirror', 
        'rightMirror', 
        'config',
        'ltrMirrorLabels', 
        'rtlMirrorLabels'
      ])
    },
    watch: {
      async drawlerData(newValue) {
        if (!newValue) {
          this.reset();
          this.loading = false;
          return;
        }
        this.loading = true;
        const response = await this.fetchData(newValue);
        if (response && response.status === 200) {
          const data = response.data.data;
          store.dispatch('setMirror', {
            position: 'left',
            value: {
              server: data.left.server_id,
              project: data.left.id,
            }
          });
          store.dispatch('setMirror', {
            position: 'right',
            value: {
              server: data.right.server_id,
              project: data.right.id,
            }
          });
          store.dispatch('setLabels', {
            direction: 'ltr',
            value: data.ltr_labels
          });
          store.dispatch('setLabels', {
            direction: 'rtl',
            value: data.rtl_labels
          });
          store.dispatch('setConfig', data.config);
        } else {
          this.reset();
        }
        this.loading = false;
      },
    },
    methods: {
      show () {
        store.dispatch('show');
      },
      close () {
        store.dispatch('close');
      },
      reset () {
        store.dispatch('setMirror', {
          position: 'left',
          value: {
            server: '',
            project: ''
          }
        });
        store.dispatch('setMirror', {
          position: 'right',
          value: {
            server: '',
            project: ''
          }
        });
        store.dispatch('setLabels', {
          direction: 'ltr',
          value: [{
            id: 1,
            left_label_id: '',
            right_label_id: ''
          }]
        });
        store.dispatch('setLabels', {
          direction: 'rtl',
          value: [{
            id: 1,
            left_label_id: '',
            right_label_id: ''
          }]
        });
      },
      submit(done) {
        const endpoint = this.drawlerData ? '/api/mirrors/' + this.drawlerData : '/api/mirrors/';
        const httpMethod = this.drawlerData ? 'put' : 'post';
        this.$http[httpMethod](endpoint, {
          left: this.leftMirror,
          right: this.rightMirror,
          ltrLabelsMap: this.ltrMirrorLabels,
          rtlLabelsMap: this.rtlMirrorLabels,
          config: this.config
        }).then(response => {
            Message.success('New server added.');
            this.$router.go();
        }).catch(error => {
          const data = error.response.data;
          if (data.message) {
            Message.error(data.message);
          }
          if (data.errors) {
            this.errors = data.errors;
          }
        });
      },
      fetchData: function (id) {
        return this.$http.get('/api/mirrors/' + id)
          .then(response => {              
            return response;
          });
      },
      onConfigChange (value) {
        store.dispatch('setConfig', value);
      }
    },
    mounted() {
      this.$refs.drawer.closeDrawer = () => {
        this.close();
      }
    }
  };
</script>
