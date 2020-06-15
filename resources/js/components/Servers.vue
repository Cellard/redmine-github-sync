<template>
    <div>

        <router-link class="float-right" style="margin-bottom: 1em;" :to="{ name: 'servers.create' }">Add server</router-link>

        <el-table :data="servers" style="width: 100%">

            <el-table-column label="Server" width="200px">
                <template slot-scope="scope">
                    <router-link :to="{ name: 'servers.edit', params: {id: scope.row.id} }">{{ scope.row.id }}</router-link>
                </template>
            </el-table-column>

            <el-table-column label="Driver" prop="driver" width="150px"></el-table-column>

            <el-table-column prop="credential.api_key" label="Access Key"></el-table-column>

            <el-table-column label="URL" width="250px">
                <template slot-scope="scope">
                    <a :href="scope.row.base_uri" target="_blank">{{ scope.row.base_uri }}</a>
                </template>
            </el-table-column>

        </el-table>

    </div>
</template>

<style>

</style>

<script>
    export default {
        name: 'Servers',
        data() {
            return {
                servers: []
            }
        },
        created: function () {
            this.fetchData();
        },
        watch: {
            '$route': 'fetchData'
        },
        methods: {
            fetchData: function () {
                this.$http.get('/api/servers/')
                    .then(response => {
                        this.servers = response.data.data;
                    });
            }
        }
    }
</script>
