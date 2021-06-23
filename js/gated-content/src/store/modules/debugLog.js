export default {
  actions: {
    debugLog(context, payload) {
      if (context.getters.getAppSettings.peerjsDebug > 0) {
        console.log(...payload);
      }
    },
  },
};
