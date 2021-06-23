import dayjs from 'dayjs';

const DayjsHelper = {
  // eslint-disable-next-line no-unused-vars
  install(Vue, options) {
    // eslint-disable-next-line no-param-reassign
    Vue.prototype.$dayjs = {
      date(value) {
        let date = dayjs(value);
        if (typeof value === 'number') {
          date = dayjs.unix(value);
        }
        return date;
      },
    };
  },
};

export default DayjsHelper;
