import { createApp } from 'vue';
import NProgress from 'nprogress';
import router from '@/router';
import { handleErrors } from '@/lib/http';
import App from '@/App.vue';

// Spinner only
NProgress.configure({ showBar: false });

handleErrors(router);

// The estate the admin works on: key (isometry), name (header), exteriors
// (list columns)
const el = document.getElementById('app');
createApp(App)
  .provide('estateKey', el.dataset.estate)
  .provide('estateName', el.dataset.estateName)
  .provide('exteriors', JSON.parse(el.dataset.exteriors))
  .use(router)
  .mount(el);
