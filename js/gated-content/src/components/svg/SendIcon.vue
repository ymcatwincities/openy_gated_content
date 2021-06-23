<template>
  <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="th"
       class="svg-inline--fa fa-th fa-w-16"
       role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="color">
    <path d="M0 0h24v24H0z" fill="none"/><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
  </svg>
</template>

<script>
export default {
  name: 'SendIcon',
  props: {
    color: {
      type: String,
      required: true,
    },
  },
};
</script>
