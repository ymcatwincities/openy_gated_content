<template>
  <div class="top-menu" :class="{menuOpen: menuOpen}" :style="backgroundStyleObject">
    <div>
      <router-link
        :to="{ name: 'Home' }"
        :style="fontStyleObject"
        @click.native="menuOpen=false"
      >Virtual YMCA</router-link>
      <button @click="menuOpen = !menuOpen" :style="fontStyleObject"></button>
      <router-link
        :to="{ name: 'Home' }"
        :style="fontStyleObject"
        @click.native="menuOpen=false"
      >
        <ScheduleIcon :color="fontColor"></ScheduleIcon>
        Schedule
      </router-link>
      <router-link
        :to="{ name: 'Home' }"
        :style="fontStyleObject"
        @click.native="menuOpen=false"
      >
        <FavoritesIcon :color="fontColor"></FavoritesIcon>
        Favorites
      </router-link>
      <router-link
        :to="{ name: 'Category', params: { type: 'video' } }"
        :style="fontStyleObject"
        @click.native="menuOpen=false"
      >
        <CategoriesIcon :color="fontColor"></CategoriesIcon>
        Categories
      </router-link>
      <a
        href="/vy-user/logout"
        :style="fontStyleObject"
        @click.native="menuOpen=false"
      >
        <LogoutIcon :color="fontColor"></LogoutIcon>
        Sign out
      </a>
    </div>
  </div>
</template>

<script>
import ScheduleIcon from '@/components/svg/ScheduleIcon.vue';
import FavoritesIcon from '@/components/svg/FavoritesIcon.vue';
import CategoriesIcon from '@/components/svg/CategoriesIcon.vue';
import LogoutIcon from '@/components/svg/LogoutIcon.vue';
import { mapGetters, mapState } from 'vuex';

export default {
  name: 'TopMenu',
  components: {
    LogoutIcon,
    CategoriesIcon,
    FavoritesIcon,
    ScheduleIcon,
  },
  data() {
    return {
      menuOpen: false,
    };
  },
  computed: {
    ...mapGetters([
      'getAppSettings',
    ]),
    ...mapState([
      'route',
    ]),
    fontColor() {
      if (this.getAppSettings && this.getAppSettings.top_menu) {
        return this.route.meta.darkMenu && !this.menuOpen
          ? this.getAppSettings.top_menu.links_color_dark
          : this.getAppSettings.top_menu.links_color_light;
      }

      return 'white';
    },
    fontStyleObject() {
      return {
        color: this.fontColor,
      };
    },
    backgroundStyleObject() {
      if (!this.getAppSettings || !this.getAppSettings.top_menu) {
        return { 'background-color': 'black' };
      }

      return {
        'background-color': this.route.meta.darkMenu && !this.menuOpen
          ? this.getAppSettings.top_menu.background_color_dark
          : this.getAppSettings.top_menu.background_color_light,
      };
    },
  },
  mounted() {
  },
};
</script>
