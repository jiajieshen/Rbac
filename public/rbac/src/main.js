// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import VueRouter from 'vue-router'
import VueResource from 'vue-resource'

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-default/index.css'

import App from './App'
import SignIn from './components/SignIn'
import Main from './components/Main'

Vue.use(VueRouter)
Vue.use(VueResource)
Vue.use(ElementUI)

const routes = [{
  path: '/',
  component: App,
  children: [{
    path: '/',
    component: Main
  }, {
    path: '/signIn',
    component: SignIn
  }, {
    path: '/main',
    component: Main
  }
  ]
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
