<template>
  <el-container id="container">
    <el-aside width="200px" style="background-color: rgb(238, 241, 246)">
      <el-menu :router="true">
        <el-menu-item index="1" :route="{name: 'servers.index'}">
          <i class="el-icon-menu"></i>
          <span>Servers</span>
        </el-menu-item>
      </el-menu>
    </el-aside>

    <el-container>
      <el-main>
        <router-view></router-view>
      </el-main>
      <el-footer>&copy; 101 media, {{new Date().getFullYear()}}</el-footer>
    </el-container>
  </el-container>
</template>

<script>
  export default {
    data() {
      return {
        openMenu: window.openMenu,
      }
    }
  }
</script>
