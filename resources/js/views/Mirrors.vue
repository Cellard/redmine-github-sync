<template>
  <div>

    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/' }">Home</el-breadcrumb-item>
      <el-breadcrumb-item>Mirrors</el-breadcrumb-item>
    </el-breadcrumb>

    <mirror-form-drawler></mirror-form-drawler>
    
    <el-table :data="servers" style="width: 100%">

      <el-table-column label="Server" prop="id" width="200px"></el-table-column>

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
      fetchData: function () {
        this.$http.get('/api/mirrors/')
          .then(response => {
            this.servers = response.data.data;
            store.dispatch('finishLoading');
            console.log(store);
            
          });
      }
    },
    mounted () {
      this.fetchData();
    }
  }
</script>
