import axios from 'axios';

export default axios.create({
  baseURL: window.drupalSettings.path.baseUrl,
  headers: {
    'Content-type': 'application/json',
  },
});
