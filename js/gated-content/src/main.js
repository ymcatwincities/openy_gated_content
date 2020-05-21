import Vue from 'vue';
import App from './GatedContent.vue';
import router from './router';
import store from './store';

Vue.config.productionTip = false;

new Vue({
  router,
  store,
  components: {
    App,
  },
}).$mount('#gated-content');
