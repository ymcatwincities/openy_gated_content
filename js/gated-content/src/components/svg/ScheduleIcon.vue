<template>
  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 18">
    <path id="Live_Icon" data-name="Live Icon"
          d="M20,0H2A2.006,2.006,0,0,0,0,2V16a2.006,2.006,0,0,0,2,2H20a2.006,2.006,0,0,0,
          2-2V2A2.006,2.006,0,0,0,20,0ZM6,12H2V6H3.5v4.5H6Zm2.75,0H7.25V6h1.5ZM13,12H11.5L9.75,
          6h1.5l1,3.43,1-3.43h1.5Zm7-4.74H17.5V8.38H20V9.64H17.5v1.11H20V12H16V6h4Z"
          :fill="color"/>
  </svg>
</template>

<script>
export default {
  name: 'ScheduleIcon',
  props: {
    color: {
      type: String,
      required: true,
    },
  },
};
</script>
