<template>
  <div>

    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/' }">Home</el-breadcrumb-item>
      <el-breadcrumb-item>Servers</el-breadcrumb-item>
    </el-breadcrumb>

    <server-form-drawler></server-form-drawler>
    
    <el-table :data="servers" style="width: 100%">

      <el-table-column label="Name" prop="name" width="200px"></el-table-column>

      <el-table-column label="Driver" prop="driver" width="150px"></el-table-column>

      <el-table-column prop="credential.api_key" label="Access Key"></el-table-column>

      <el-table-column label="URL" width="250px">
        <template slot-scope="scope">
          <a :href="scope.row.base_uri" target="_blank">{{ scope.row.base_uri }}</a>
        </template>
      </el-table-column>

      <el-table-column
        fixed="right"
        label="Operations"
        width="120">
        <template slot-scope="scope">
          <el-button @click="showDrawler(scope.row.id)" type="text">Edit</el-button>
          <el-button @click="deleteServer(scope.row.id)" style="color: #F56C6C" type="text">Delete</el-button>
        </template>
      </el-table-column>

    </el-table>

  </div>
</template>

<script>
  import { store } from '../store'

  export default {
    name: 'Servers',
    data() {
      return {
        servers: [],
        drawlerVisible: false
      }
    },
    created: function () {
      this.fetchData();
    },
    watch: {
      '$route': 'fetchData'
    },
    methods: {
      showDrawler: function (data = null) {
        store.dispatch('show', data);
      },
      deleteServer: function (id) {
        this.$confirm('This will permanently delete the server. Continue?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning'
        }).then(() => {
          this.$http.delete('/api/servers/' + id)
          .then(response => {
            this.$router.go();
          });
        });
      },
      fetchData: function () {
        this.$http.get('/api/servers/')
          .then(response => {
            this.servers = response.data.data;
            store.dispatch('finishLoading');
          });
      }
    },
    mounted () {
      this.fetchData();
    }
  }
</script>
