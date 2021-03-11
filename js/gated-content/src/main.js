import Vue from 'vue';
import VueCookies from 'vue-cookies';
import { sync } from 'vuex-router-sync';
import updateLocale from 'dayjs/plugin/updateLocale';
import duration from 'dayjs/plugin/duration';
import advancedFormat from 'dayjs/plugin/advancedFormat';
import dayjs from 'dayjs';
import App from './GatedContent.vue';
import router from './router';
import store from './store';
import filters from './filters';
import Log from './plugins/log';
import 'core-js/stable';
import 'regenerator-runtime/runtime';

Vue.use(VueCookies);

Vue.use(Log);

Vue.config.productionTip = false;

sync(store, router);

filters.forEach((f) => {
  Vue.filter(f.name, f.execute);
});

dayjs.extend(duration);
dayjs.extend(advancedFormat);
dayjs.extend(updateLocale);
dayjs.updateLocale('en', {
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
  watch: {
    $route(to) {
      this.$log.trackActivity({ path: to.fullPath });
    },
  },
  mounted() {
    const app = this;

    const cookieName = 'openy_gc_auth_destination';
    if (this.$cookies.isKey(cookieName)) {
      window.location.hash = this.$cookies.get(cookieName);
      this.$cookies.remove(cookieName);
    }

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
