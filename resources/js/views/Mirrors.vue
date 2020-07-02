<template>
  <div>

    <el-breadcrumb separator-class="el-icon-arrow-right">
      <el-breadcrumb-item :to="{ path: '/' }">Home</el-breadcrumb-item>
      <el-breadcrumb-item>Mirrors</el-breadcrumb-item>
    </el-breadcrumb>

    <mirror-form-drawler></mirror-form-drawler>
    
    <el-table :data="mirrors" style="width: 100%">

      <el-table-column label="Left" width="200px">
        <template slot-scope="scope">
          <span>{{ scope.row.left.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Left url">
        <template slot-scope="scope">
          <span>{{ scope.row.left.server.base_uri }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Right">
        <template slot-scope="scope">
          <span>{{ scope.row.right.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Right url">
        <template slot-scope="scope">
          <span>{{ scope.row.right.server.base_uri }}</span>
        </template>
      </el-table-column>

      <el-table-column
        fixed="right"
        label="Operations"
        width="120">
        <template slot-scope="scope">
          <el-button @click="showDrawler(scope.row.id)" type="text">Edit</el-button>
          <el-button @click="deleteMirror(scope.row.id)" style="color: #F56C6C" type="text">Delete</el-button>
        </template>
      </el-table-column>

    </el-table>
  </div>
</template>

<script>
  import { store } from '../store'

  export default {
    name: 'Mirrors',
    data() {
      return {
        deleteDialog: {
          visible: false,
          mirrorId: null
        },
        mirrors: [],
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
      deleteMirror: function (id) {
        this.$confirm('This will permanently delete the mirror. Continue?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning'
        }).then(() => {
          this.$http.delete('/api/mirrors/' + id)
          .then(response => {
            this.$router.go();
          });
        });
      },
      fetchData: function () {
        this.$http.get('/api/mirrors/')
          .then(response => {
            this.mirrors = response.data.data;
            store.dispatch('finishLoading');            
          });
      }
    },
    mounted () {
      this.fetchData();
    }
  }
</script>
