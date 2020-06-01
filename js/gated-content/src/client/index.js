import axios from 'axios';
import qs from 'qs';

export default axios.create({
  baseURL: window.drupalSettings.path.baseUrl,
  headers: {
    'Content-type': 'application/json',
  },
  paramsSerializer: (params) => {
    return qs.stringify(params);
  },
});
