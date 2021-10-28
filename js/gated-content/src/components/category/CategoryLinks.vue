<template>
  <ul v-if="isCategoriesLoaded">
    <li v-for="category in $store.getters.getAncestors(tid)" :key="category.tid">
      <router-link :to="{ name: 'Category', params: { id: category.uuid }}">
        {{ category.label }}
      </router-link>
    </li>
  </ul>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  name: 'CategoryLinks',
  props: {
    tid: {
      type: Number,
      required: true,
    },
  },
  computed: {
    ...mapGetters([
      'isCategoriesLoaded',
    ]),
  },
};
</script>
