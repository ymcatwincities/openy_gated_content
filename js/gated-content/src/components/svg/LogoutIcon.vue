<template>
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20.001" viewBox="0 0 20 20.001">
    <path id="Sign_Out_Icon" data-name="Sign Out Icon"
          d="M123,40a9.926,9.926,0,0,1,1.656-5.515L123.1,32.929l1.414-1.414,1.415,1.415h0l4.358,
          4.358h0l1.421,1.42h0l2.256,2.256h0l4.124,4.124h0l1.979,1.98h0l1.416,1.416L140.071,
          49.9l-1.555-1.555A10,10,0,0,1,123,40Zm4,3.981a7.207,7.207,0,0,0,9.478,
          2.326l-5.216-5.216C129.286,41.484,127.022,42.473,127,43.98Zm14.341,1.533-6.888-6.887a3,
          3,0,1,0-4.08-4.081l-2.888-2.888a10,10,0,0,1,13.856,13.855h0Z"
          transform="translate(-123 -30)" :fill="color"/>
  </svg>
</template>

<script>
export default {
  name: 'LogoutIcon',
  props: {
    color: {
      type: String,
      required: true,
    },
  },
};
</script>
