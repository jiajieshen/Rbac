<template>
    <div id="root">
        <el-row type="flex" class="navbar row-bg">
            <el-col :span="6">
                <a class="nav-item is-hidden-tablet" @click="toggleSidebar(!sidebar.opened)">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </a>
            </el-col>
            <el-col :span="6">
                <div class="nav-right">
                    <a class="navbar-brand" href="#">
                        <i class="glyphicon glyphicon-time"></i>
                        计划板
                    </a>
                    <ul class="nav navbar-nav">
                        <li>
                            <router-link to="/home">首页</router-link>
                        </li>
                        <li>
                            <router-link to="/time-entries">计划列表</router-link>
                        </li>
                        <li>
                            <router-link to="/hello">Hello</router-link>
                        </li>
                    </ul>
                </div>
            </el-col>
        </el-row>
        <sidebar></sidebar>
        <div class="container">
            <div class="col-sm-3">

            </div>
            <div class="col-sm-9">
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>

<script type="es6">
    import Sidebar from './Sidebar.vue'

    export default {
        components: {
            Sidebar
        },
        mounted: function () {
           /* this.getMenu()*/
        },
        methods: {
            /*getMenu: function () {
                this.$http.get('http://rbac.xyz/admin/admin_menu/getMenus')
                        .then((response) => {
                            console.log('success')
                        }, (response)=> {
                            console.log('error')
                        })
                        .catch(function (response) {
                            console.log(response)
                        })
            }*/
        }
    }

</script>

<style>
    .navbar {
        min-width: 100%;
        z-index: 1024;
        box-shadow: 3px 3px 3px #888888;
        background: #20a0ff;
    }
</style>