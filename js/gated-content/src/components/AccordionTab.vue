<template>
  <div class="accordion-tab">
    <div class="accordion-tab__header"
    @click="toggle"
    >
      <div>{{ title }}</div>
      <div>{{ open ? '-' : '+' }}</div>
    </div>
    <div class="accordion-tab__content"
         v-if="content && open"
         v-html="content"
    ></div>
    <div
      v-else-if="open"
      class="accordion-tab__content"
    >
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AccordionTab',
  props: {
    title: {
      type: String,
      default: 'Tab Title',
    },
    content: {
      type: String,
      default: undefined,
    },
  },
  data() {
    return {
      open: false,
    };
  },
  methods: {
    toggle() {
      this.open = !this.open;
    },
  },
};
</script>
