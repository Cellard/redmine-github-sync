<template>
  <div>

    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/' }">Home</el-breadcrumb-item>
    </el-breadcrumb>
    
    <el-table :data="logs" style="width: 100%">

      <el-table-column type="expand">
        <template slot-scope="scope">
          <div v-if="scope.row.errors.length">
            <p style="color: #f56c6c" :key="error.id" v-for="error in scope.row.errors">
              {{error.message}}
            </p>
          </div>
          <div v-else>
            <p>
              No data
            </p>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="Mirror">
        <template slot-scope="scope">
          {{ scope.row.mirror.left.name }} - {{ scope.row.mirror.right.name }}
        </template>
      </el-table-column>

      <el-table-column label="Status">
        <template slot-scope="scope">
          <el-tag v-if="scope.row.status === 'Finished with errors'" type="danger">{{scope.row.status}}</el-tag>
          <el-tag v-else-if="scope.row.status === 'Success'" type="success">{{scope.row.status}}</el-tag>
          <el-tag v-else-if="scope.row.status === 'In process'">{{scope.row.status}}</el-tag>
          <el-tag v-else type="info">{{scope.row.status}}</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Updated">
        <template slot-scope="scope">
          {{ formatDate(scope.row.updated_at) }}
        </template>
      </el-table-column>

      <el-table-column label="Created">
        <template slot-scope="scope">
          {{ formatDate(scope.row.created_at) }}
        </template>
      </el-table-column>

    </el-table>
    

  </div>
</template>

<script>
  import { store } from '../store';
  import moment from 'moment';

  export default {
    name: 'Logs',
    data() {
      return {
        logs: []
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
        this.$http.get('/api/logs/')
          .then(response => {
            this.logs = response.data.data;
            console.log(this.logs);
            
            store.dispatch('finishLoading');
          });
      },
      formatDate: function (date) {
        return moment(date).format('DD.MM.YYYY hh:mm:ss');
      }
    },
    mounted () {
      this.fetchData();
    }
  }
</script>
