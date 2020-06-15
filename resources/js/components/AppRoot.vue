<template>
    <div id="router">

        <ul>
            <li><router-link :to="{name:'servers.index'}">Servers</router-link></li>
        </ul>

        <router-view></router-view>

    </div>
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
