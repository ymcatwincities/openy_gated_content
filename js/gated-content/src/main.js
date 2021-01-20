import Vue from 'vue';
import { sync } from 'vuex-router-sync';
import moment from 'moment';
import App from './GatedContent.vue';
import router from './router';
import store from './store';
import filters from './filters';
import Log from './plugins/log';
import 'core-js/stable';
import 'regenerator-runtime/runtime';

Vue.use(Log);

Vue.config.productionTip = false;

sync(store, router);

filters.forEach((f) => {
  Vue.filter(f.name, f.execute);
});

moment.updateLocale('en', {
  meridiem(hours) {
    return hours < 12 ? 'a.m.' : 'p.m.';
  },
});

new Vue({
  router,
  store,
  components: {
    App,
  },
  mounted() {
    const app = this;

    if ('-ms-scroll-limit' in document.documentElement.style
      && '-ms-ime-align' in document.documentElement.style) {
      window.addEventListener('hashchange',
        () => {
          const currentPath = window.location.hash.slice(1);
          if (app.$route.path !== currentPath) {
            app.$router.replace(currentPath);
          }
        },
        false);
    }
  },
}).$mount('#gated-content');
