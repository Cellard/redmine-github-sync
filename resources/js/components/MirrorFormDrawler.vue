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
          <h4>Labels</h4>
        </el-divider>
        <mirror-labels-form></mirror-labels-form>

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
      ...mapGetters(['drawlerVisibility', 'drawlerData', 'leftMirror', 'rightMirror', 'mirrorLabels'])
    },
    watch: {
      async drawlerData(newValue) {
        if (!newValue) {
          this.reset();
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
          store.dispatch('setLabels', data.labels);
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
        store.dispatch('setLabels', []);
      },
      submit(done) {
        const endpoint = this.drawlerData ? '/api/mirrors/' + this.drawlerData : '/api/mirrors/';
        const httpMethod = this.drawlerData ? 'put' : 'post';
        this.$http[httpMethod](endpoint, {
          left: this.leftMirror,
          right: this.rightMirror,
          labels: this.mirrorLabels
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
    },
    mounted() {
      this.$refs.drawer.closeDrawer = () => {
        this.close();
      }
    }
  };
</script>
