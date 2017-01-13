// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import VueRouter from 'vue-router'
import VueResource from 'vue-resource'

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-default/index.css'

import App from './App'
import Home from './components/Home'
import SignIn from './components/SignIn'
import Hello from './components/Hello'

Vue.use(VueRouter)
Vue.use(VueResource)
Vue.use(ElementUI)

const routes = [{
  path: '/',
  component: Home
}, {
  path: '/signIn',
  component: SignIn
}, {
  path: '/home',
  component: Home
}, {
  path: '/hello',
  component: Hello
}]

const router = new VueRouter({
  routes
})

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  template: '<App/>',
  components: {App}
})
