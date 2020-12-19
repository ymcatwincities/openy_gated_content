<template>
  <div
    :class="{ active: isActive }"
    class="add-to-favorite"
    @click="onClick"
    @mouseover="hover = true"
    @mouseleave="hover = false"
  >
    <Spinner v-if="loading"></Spinner>
    <SvgIcon v-else
      title="Add to favorite"
      class="favorite-icon"
      :css-fill=false
      :icon="currentIcon"
      :class="iconClass"
    ></SvgIcon>
  </div>
</template>

<script>
import Spinner from '@/components/Spinner.vue';
import SvgIcon from '@/components/SvgIcon.vue';

export default {
  name: 'AddToFavorite',
  components: {
    SvgIcon,
    Spinner,
  },
  props: {
    id: {
      type: Number,
      required: true,
    },
    icon: {
      type: String,
      default: 'favorites-solid',
    },
    iconActive: {
      type: String,
      default: 'favorites-solid-red',
    },
    iconClass: {
      type: String,
    },
    type: {
      type: String,
      required: true,
    },
    bundle: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      loading: false,
      shareModal: {
        visible: false,
      },
      hover: false,
    };
  },
  computed: {
    isActive() {
      return this.$store.getters.isFavorite({
        id: this.id,
        type: this.type,
        bundle: this.bundle,
      });
    },
    currentIcon() {
      if (this.isActive) {
        return this.iconActive;
      }

      if (this.hover === true) {
        return this.iconActive;
      }

      return this.icon;
    },
  },
  methods: {
    onClick() {
      this.loading = true;
      setTimeout(this.addToFavorite, 1000);
    },
    addToFavorite() {
      let action = 'addItemToFavorites';
      if (this.isActive) {
        action = 'deleteItemFromFavorites';
      }
      this.$store.dispatch(action, {
        id: this.id,
        type: this.type,
        bundle: this.bundle,
      })
        .then(() => {
          this.loading = false;
        });
    },
  },
};
</script>
