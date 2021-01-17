<template>
  <div class="top-menu" :class="{menuOpen: menuOpen}" :style="backgroundStyleObject">
    <div class="gated-containerV2 px--20-10">
      <div @click="menuOpen=false">
        <router-link
          :to="{ name: 'Home' }"
          :style="fontStyleObject"
          exact
        >Virtual YMCA
        </router-link>
      </div>
      <button
        @click="menuOpen = !menuOpen"
        :style="fontStyleObject"
        :class="{ opened: menuOpen  }"
      >
        <MenuIcon
          v-if="!menuOpen"
          :color="fontColor"
        ></MenuIcon>
        <CloseIcon
          v-else
          :color="fontColor"
        ></CloseIcon>
      </button>
      <div @click="menuOpen=false">
        <router-link
          :to="{ name: 'Schedule' }"
          :style="fontStyleObject"
          exact
        >
          <ScheduleIcon :color="fontColor"></ScheduleIcon>Schedule
        </router-link>
      </div>
      <div @click="menuOpen=false">
        <router-link
          :to="{ name: 'Favorites' }"
          :style="fontStyleObject"
          exact
        >
          <FavoritesIcon :color="fontColor"></FavoritesIcon>Favorites
        </router-link>
      </div>
      <div @click="menuOpen=false">
        <router-link
          :to="{ name: 'CategoryListing', params: { type: 'video' } }"
          :style="fontStyleObject"
          exact
        >
          <CategoriesIcon :color="fontColor"></CategoriesIcon>Categories
        </router-link>
      </div>
      <div @click="menuOpen=false">
        <a
          href="/vy-user/logout"
          :style="fontStyleObject"
        >
          <LogoutIcon :color="fontColor"></LogoutIcon>Sign out
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import ScheduleIcon from '@/components/svg/ScheduleIcon.vue';
import FavoritesIcon from '@/components/svg/FavoritesIcon.vue';
import CategoriesIcon from '@/components/svg/CategoriesIcon.vue';
import LogoutIcon from '@/components/svg/LogoutIcon.vue';
import { mapGetters, mapState } from 'vuex';
import MenuIcon from '@/components/svg/MenuIcon.vue';
import CloseIcon from '@/components/svg/CloseIcon.vue';

export default {
  name: 'TopMenu',
  components: {
    CloseIcon,
    MenuIcon,
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
        'border-color': this.fontColor,
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
};
</script>
