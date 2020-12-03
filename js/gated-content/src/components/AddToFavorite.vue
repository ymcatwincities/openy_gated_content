<template>
  <div
    :class="{ active: isActive, 'add-to-favorite': true }"
    class="fa-stack fa-50"
  >
    <Spinner v-if="loading"></Spinner>
    <template v-else>
      <i class="fas fa-heart fa-stack-1x" title="Add to favorite" @click="onClick"></i>
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

<style lang="scss">
.add-to-favorite {
  cursor: pointer;
  display: inline-block;
  margin: 0 20px;
  font-size: 1.5rem;

  .spinner {
    width: 25px;
    height: 25px;
  }

  &.active {
    color: $red;
  }

  &:hover {
    color: $red;
  }
}
</style>
