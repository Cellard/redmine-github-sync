<template>
  <el-form :inline="true" :rules="rules" ref="mirrorForm" :model="mirror" label-position="top">
    <el-form-item :error="errors.server ? errors.server[0] : ''" label="Server" prop="server">
      <el-select filterable @change="onServerChange" v-model="mirror.server" placeholder="Select">
        <el-option
          v-for="item in servers"
          :key="item.id"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>
    </el-form-item>
    <el-form-item :error="errors.project ? errors.project[0] : ''" label="Project" prop="project">
      <el-select filterable @change="fetchMilestones" :disabled="!projects.length" v-model="mirror.project" placeholder="Select">
        <el-option
          v-for="item in projects"
          :key="item.id"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>
    </el-form-item>
    <el-form-item :error="errors.milestone ? errors.milestone[0] : ''" label="Milestone (Version)" prop="milestone">
      <el-select filterable :disabled="!milestones.length" v-model="mirror.milestone" placeholder="Select">
        <el-option
          v-for="item in milestones"
          :key="item.id"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>
    </el-form-item>
  </el-form>
</template>

<script>
  import { store } from '../store'
  import { mapGetters } from 'vuex';

  export default {
    props: ['position'],
    data() {
      return {
        loading: true,
        direction: 'rtl',
        servers: [],
        projects: [],
        milestones: [],
        rules: {
          server: [
            { required: true, message: 'Please select server', trigger: 'blur' }
          ],
          project: [
            { required: true, message: 'Please select project', trigger: 'blur' }
          ]
        },
        errors: {}
      };
    },
    computed: {
      ...mapGetters(['leftMirror', 'rightMirror']),
      mirror: function () {
        return this.position === 'left' ? this.leftMirror : this.rightMirror;
      }
    },
    watch: {
      async mirror(newValue) {
        this.fetchProjects(newValue.server);
      },
      async project(newValue) {
        this.fetchMilestones(newValue.project);
      },
    },
    methods: {
      fetchMilestones: function (projectId) {
        if (projectId) {
          this.$http.get('/api/projects/' + projectId + '/milestones')
            .then(response => {
              this.milestones = response.data.data;
            });
        } else {
          this.milestones = []
        }
      },
      fetchServers: function () {
        this.$http.get('/api/servers')
          .then(response => {
            this.servers = response.data.data;
          });
      },
      onServerChange: function (id) {
        store.dispatch('setMirror', {
          position: this.position,
          value: {
            server: this.mirror.server,
            project: ''
          }
        });
        this.fetchProjects(id);
      },
      fetchProjects: function (id) {
        if (id) {
          this.$http.get('/api/servers/' + id + '/projects')
            .then(response => {
              this.projects = response.data.data;
            });
          if (this.mirror.project) {
            this.fetchMilestones(this.mirror.project);
          }
        } else {
          this.projects = [];
          this.milestones = [];
        }
      }
    },
    mounted() {
      this.fetchServers();
    }
  };
</script>
