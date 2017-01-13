<template>
    <div class="jumbotron">
        <h1>任务追踪</h1>
        <p>
            <strong>
                <router-link to="/time-entries">创建一个任务</router-link>
            </strong>
            <el-button @click="login">Button</el-button>
        </p>
    </div>
</template>

<script>
export default {
  methods: {
    login: function (event) {

    }
  }
}
</script>