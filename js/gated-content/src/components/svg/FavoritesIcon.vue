<template>
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="18.35" viewBox="0 0 20 18.35">
    <path id="Favorites_Icon_Full" data-name="Favorites Icon Full"
          d="M12,21.35l-1.45-1.32C5.4,15.36,2,12.28,2,8.5A5.447,5.447,0,0,1,7.5,3,5.988,5.988,0,0,1,
          12,5.09,5.988,5.988,0,0,1,16.5,3,5.447,5.447,0,0,1,22,8.5c0,3.78-3.4,6.86-8.55,11.54Z"
          transform="translate(-2 -3)" :fill="color"/>
  </svg>

</template>

<script>
export default {
  name: 'FavoritesIcon',
  props: {
    color: {
      type: String,
      required: true,
    },
  },
};
</script>
