<template>
  <div
    :class="{ active: isActive }"
    class="add-to-favorite"
  >
    <Spinner v-if="loading"></Spinner>
    <template v-else>
      <span title="Add to favorite" @click="onClick">
        <svg width="1em" height="1em" viewBox="0 0 16 16" class="favorite-icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path d="M4 1c2.21 0 4 1.755 4 3.92C8 2.755 9.79 1 12 1s4 1.755 4 3.92c0 3.263-3.234
          4.414-7.608 9.608a.513.513 0 0 1-.784 0C3.234 9.334 0 8.183 0 4.92 0 2.755 1.79 1 4 1z"/>
        </svg>
      </span>
    </template>
  </div>
</template>

<script>
import Spinner from '@/components/Spinner.vue';

export default {
  name: 'AddToFavorite',
  components: {
    Spinner,
  },
  props: {
    id: {
      type: Number,
      required: true,
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
