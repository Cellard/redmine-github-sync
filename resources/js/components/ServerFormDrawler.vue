<template>
  <div>

    <el-button @click="show()" style="margin-bottom: 10px" type="primary" icon="el-icon-circle-plus">Add server</el-button>

    <el-drawer
      :title="drawlerData || 'New Server'"
      :visible.sync="drawlerVisibility"
      :direction="direction"
      ref="drawer">
      <div v-loading="loading" class="drawer__content">
        <el-form :rules="rules" ref="serverForm" :model="server" label-position="top">
          <el-form-item :error="errors.url ? errors.url[0] : ''" label="Url" prop="url">
            <el-input v-model="server.url" autocomplete="off"></el-input>
          </el-form-item>
          <el-form-item :error="errors.driver ? errors.driver[0] : ''" label="Driver" prop="driver">
            <el-select v-model="server.driver" placeholder="Select">
              <el-option
                v-for="item in drivers"
                :key="item.id"
                :label="item.name"
                :value="item.id">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item :error="errors.driver ? errors.key[0] : ''" label="API key" prop="key">
            <el-input v-model="server.key" autocomplete="off"></el-input>
          </el-form-item>
        </el-form>
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
        drivers: [],
        formLoading: false,
        server: {
          url: '',
          driver: '',
          key: ''
        },
        rules: {
          url: [
            { required: true, message: 'Please input server Url', trigger: 'blur' }
          ],
          driver: [
            { required: true, message: 'Please input server driver', trigger: 'blur' }
          ],
          key: [
            { required: true, message: 'Please input your API key', trigger: 'blur' }
          ],
        },
        errors: {}
      };
    },
    computed: {
      ...mapGetters(['drawlerVisibility', 'drawlerData'])
    },
    watch: {
      async drawlerData(newValue) {
        this.loading = true;
        const response = await this.fetchData(newValue);
        if (response && response.status === 200) {
          const data = response.data.data;
          this.server.url = data.base_uri;
          this.server.driver = data.driver;
          this.server.key = data.credential ? data.credential.api_key : '';
        } else {
          this.server = {}
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
        this.$refs.serverForm.resetFields();
      },
      submit(done) {
        this.$refs.serverForm.validate((valid) => {
          if (valid) {
            const endpoint = this.drawlerData ? '/api/servers/' + this.drawlerData : '/api/servers/';
            const httpMethod = this.drawlerData ? 'put' : 'post';
            this.$http[httpMethod](endpoint, {
              url: this.server.url,
              driver: this.server.driver,
              api_key: this.server.key
            }).then(response => {
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
          } else {
            return false;
          }
        });
      },
      fetchData: function (id) {
        if (id) {
          return this.$http.get('/api/servers/' + id)
            .then(response => {
              return response;
            });
        } else {
          return null;
        }
      },
      fetchDrivers: function () {
        this.$http.get('/api/drivers')
          .then(response => {
            this.drivers = response.data.data;
          });
      }
    },
    mounted() {
      this.$refs.drawer.closeDrawer = () => {
        this.close();
      }
      this.fetchDrivers();
    }
  };
</script>
