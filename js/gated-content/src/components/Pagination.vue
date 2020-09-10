<template>
  <nav class="text-center">
    <span v-if="currentPage === 0 && itemsCount < pageLimit" class="d-none"></span>
    <ul class="pagination justify-content-center m-4" v-else>
      <li class="page-item" :class="{ disabled: disablePrev }">
        <router-link
          :to="paginateObject(currentPage - 1)"
          class="page-link prev"
        >Prev</router-link>
      </li>
      <li class="page-item active">
        <span class="page-link">{{ currentPage + 1 }}</span>
      </li>
      <li class="page-item" :class="{ disabled: disableNext }">
        <router-link
          :to="paginateObject(currentPage + 1)"
          class="page-link next"
        >Next</router-link>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: 'Pagination',
  data() {
    return {
      currentPage: null,
    };
  },
  props: {
    pageParameter: {
      type: String,
      default: 'page',
    },
    links: {
      type: Object,
    },
    itemsCount: {
      type: Number,
      default: 0,
    },
    pageLimit: {
      type: Number,
      default: 12,
    },
  },
  computed: {
    disablePrev() {
      return typeof this.links.prev === 'undefined';
    },
    disableNext() {
      return typeof this.links.next === 'undefined';
    },
  },
  methods: {
    paginateObject(pageTo) {
      return {
        query: {
          ...this.$route.query,
          [this.pageParameter]: pageTo,
        },
      };
    },
  },
  mounted() {
    this.currentPage = parseInt(this.$route.query[this.pageParameter], 10) || 0;
  },
  watch: {
    $route(to) {
      this.currentPage = parseInt(to.query[this.pageParameter], 10) || 0;
    },
  },
};
</script>
