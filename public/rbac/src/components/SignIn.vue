<template>
    <div class="layer-signin">
        <h2 class="form-signin-heading">登录</h2>
        <div id="app">
            <el-input v-model="account" placeholder="账号"></el-input>
            <el-input v-model="password" placeholder="密码"></el-input>
            <el-button type="danger" @click="signIn">登录</el-button>
            <el-button type="text" @click="toSignUp">注册</el-button>
        </div>
    </div>
</template>
<script type="es6">
    export default{
        data(){
            return {
                account: 'admin',
                password: '123456'
            }
        },
        methods: {
            signIn: function () {
                
            },
            toSignUp: function () {
                
            }
        }
    }
</script>
<style>
    .layer-signin {
        background-color: #ff0000;
    }
</style>
