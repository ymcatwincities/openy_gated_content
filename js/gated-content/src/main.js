import Vue from 'vue';
import App from './GatedContent.vue';
import router from './router';
import store from './store';
import filters from './filters';
import Telemetry from './plugins/telemetry';

Vue.use(Telemetry);

Vue.config.productionTip = false;

filters.forEach((f) => {
  Vue.filter(f.name, f.execute);
});

new Vue({
  router,
  store,
  components: {
    App,
  },
}).$mount('#gated-content');
