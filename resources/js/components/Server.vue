<template>
    <div>
        <h1>{{header_caption}}</h1>

        <form>
            <label>
                <el-select v-model="server.driver">
                    <el-option v-for="driver in drivers"
                               :key="driver.id"
                               :label="driver.name"
                               :value="driver.id"></el-option>
                </el-select>
            </label>
            <label>
                <el-input placeholder="Server URL" v-model="server.base_uri"></el-input>
            </label>
            <label>
                <el-input placeholder="API Key" v-model="server.credential.api_key"></el-input>
            </label>
        </form>

    </div>
</template>

<script>
    export default {
        name: "Server",
        data() {
            return {
                server: {
                    id: null,
                    driver: null,
                    base_uri: null,
                    credential: {
                        api_key: null
                    }
                },
                /* [{id: "", name: ""}, ...] */
                drivers: []
            }
        },
        computed: {
            header_caption: function () {
                return this.id ?? 'New Server';
            }
        },
        created: function () {
            this.id = this.$route.params.id ?? null;
            this.fetchDrivers();
            this.fetchData();
        },
        methods: {
            fetchData: function () {
                if (this.id) {
                    this.$http.get('/api/servers/' + this.id)
                        .then(response => {
                            this.server = response.data.data;
                        });
                }
            },
            fetchDrivers: function () {
                this.$http.get('/api/drivers')
                    .then(response => {
                        this.drivers = response.data.data;
                    });
            }
        }
    }
</script>

<style scoped>

</style>
