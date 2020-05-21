export default {
  state: { },
  actions: {
    dummyAuthorize(context) {
      console.log('You are authorized now!');
      // Call еру base auth authorize action.
      context.dispatch('authorize', {});
    },
    dummyLogout(context) {
      console.log('Logging you out');
      // Call the base auth logout action.
      context.dispatch('logout');
    },
  },
};
